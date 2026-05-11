<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages;

use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\WorkCompletionReportResource;
use Throwable;

class SendWorkCompletionReport extends Page
{
    use InteractsWithRecord;

    protected static string $resource = WorkCompletionReportResource::class;

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return Heroicon::OutlinedPaperAirplane;
    }

    public static function getNavigationLabel(): string
    {
        return 'Send Email';
    }

    protected string $view = 'project::filament.clusters.project.resources.work-completion-reports.pages.send-work-completion-report';

    public ?array $data = [];

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        // No restriction, allow fallback PDF generation
        $this->form->fill([
            'subject' => 'Work Completion Report (BAPP) - '.$this->record->number,
            'recipient_email' => $this->record->customer?->email,
            'recipient_name' => $this->record->customer?->name,
            'message' => '<p>Please find the Work Completion Report (BAPP) for your project attached.</p><p>Please review the document. If you have any questions, feel free to contact us.</p>',
        ]);
    }

    public function getTitle(): string
    {
        return 'Send BAPP Email';
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function getContactOptions(): array
    {
        $contacts = $this->record->customer?->contacts ?? [];

        return collect($contacts)
            ->filter(fn ($contact) => is_array($contact))
            ->mapWithKeys(function ($contact) {
                $email = $contact['email'] ?? ($contact['name'] ?? 'No Email');
                $name = $contact['name'] ?? 'Unnamed';
                $value = $email.'|'.$name;
                $label = $name.($email ? " ({$email})" : '');

                return [$value => $label];
            })->toArray();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('recipient_contact')
                    ->label('Select Contact')
                    ->options(fn () => $this->getContactOptions())
                    ->live()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('email')->email()->required(),
                    ])
                    ->createOptionUsing(function (array $data) {
                        $customer = $this->record->customer;
                        $contacts = $customer->contacts ?? [];
                        $contacts[] = $data;
                        $customer->update(['contacts' => $contacts]);

                        return ($data['email'] ?? $data['name']).'|'.($data['name'] ?? '');
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state && str_contains($state, '|')) {
                            [$email, $name] = explode('|', $state);
                            $set('recipient_email', $email);
                            $set('recipient_name', $name);
                        }
                    }),
                TextInput::make('recipient_email')
                    ->label('Recipient Email')
                    ->email()
                    ->required()
                    ->live(onBlur: false),
                TextInput::make('subject')
                    ->required()
                    ->live(onBlur: false),
                RichEditor::make('message')
                    ->live(onBlur: false)
                    ->debounce(500),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to BAPP')
                ->color('gray')
                ->url(fn () => $this->getResource()::getUrl('view', [
                    'project' => $this->record->project_id,
                    'record' => $this->record,
                ])),
        ];
    }

    public function sendEmailAction(): Action
    {
        return Action::make('sendEmail')
            ->label('Send Email Now')
            ->icon('heroicon-o-paper-airplane')
            ->color('primary')
            ->requiresConfirmation()
            ->action(fn () => $this->sendEmail());
    }

    public function sendEmail(): void
    {
        $formData = $this->form->getState();

        if (! $formData || empty($formData['recipient_email'])) {
            Notification::make()
                ->title('Validation Error')
                ->body('Please ensure all required fields are filled correctly.')
                ->danger()
                ->send();

            return;
        }

        try {
            // 1. Prepare Attachment
            $attachmentUrl = null;
            $attachmentName = null;

            if ($media = $this->record->getFirstMedia('signed_report')) {
                $attachmentUrl = $media->getTemporaryUrl(now()->addMinutes(30));
                $attachmentName = $media->file_name;
            } elseif ($media = $this->record->getFirstMedia('draft_report')) {
                $attachmentUrl = $media->getTemporaryUrl(now()->addMinutes(30));
                $attachmentName = $media->file_name;
            } else {
                // Generate PDF on the fly as a fallback
                $pdf = Pdf::loadView('project::pdf.work_completion_report', ['record' => $this->record]);
                $filename = 'bapp-'.str_replace(['/', '\\'], '-', $this->record->number).'.pdf';

                // Store temporarily on S3 to get a URL
                $tempPath = "temp/bapps/{$filename}";
                Storage::disk('s3')->put($tempPath, $pdf->output(), 'private');
                $attachmentUrl = Storage::disk('s3')->temporaryUrl($tempPath, now()->addMinutes(60));
                $attachmentName = $filename;
            }

            // 2. Prepare Message
            $messageBody = view('emails.unified', [
                'body' => $formData['message'] ?? '',
                'subject' => $formData['subject'],
            ])->render();

            // 3. Send via External API
            Log::info('BAPP Email Sending Attempt', [
                'bapp_id' => $this->record->id,
                'number' => $this->record->number,
                'recipient' => $formData['recipient_email'],
            ]);

            $response = Http::timeout(60)
                ->withHeaders([
                    'content-type' => 'application/json',
                    'x-requester-app' => 'GDPS-ERP',
                ])->post('https://machine.garudapratama.com/api/v1/email/send', [
                    'to' => [
                        $formData['recipient_email'],
                    ],
                    'subject' => $formData['subject'],
                    'body' => $messageBody,
                    'attachments' => [
                        [
                            'name' => $attachmentName,
                            'url' => $attachmentUrl,
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                $errorMsg = $response->json('message') ?? $response->status();

                Log::error('BAPP Email Sending Failed', [
                    'bapp_id' => $this->record->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new Exception('Email system error: '.$errorMsg);
            }

            // 4. Update BAPP status to Sent
            $this->record->update([
                'status' => WorkCompletionStatus::Sent,
            ]);

            // 5. Create Communication Log
            $this->record->communicationLogs()->create([
                'recipient_email' => $formData['recipient_email'],
                'sender_id' => auth()->id(),
                'sender_email' => auth()->user()?->email,
                'subject' => $formData['subject'],
                'message' => $messageBody,
                'sent_at' => now(),
            ]);

            Notification::make()
                ->title('Email Sent Successfully')
                ->body('The Work Completion Report has been successfully sent to the customer.')
                ->success()
                ->send();

            $this->redirect($this->getResource()::getUrl('view', [
                'project' => $this->record->project_id,
                'record' => $this->record,
            ]));
        } catch (Throwable $e) {
            Log::error('BAPP Email Failure: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->title('Failed to Send Email')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
