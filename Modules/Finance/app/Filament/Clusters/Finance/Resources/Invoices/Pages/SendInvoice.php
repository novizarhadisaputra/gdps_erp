<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\InvoiceResource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class SendInvoice extends Page
{
    use InteractsWithRecord;

    protected static string $resource = InvoiceResource::class;

    protected string $view = 'finance::filament.clusters.finance.resources.invoices.pages.send-invoice';

    public ?array $data = [];

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->form->fill([
            'subject' => 'Invoice & BAPP '.$this->record->invoice_number,
            'recipient_email' => $this->record->customer?->email,
            'recipient_name' => $this->record->customer?->name,
            'message' => '<p>Kepada Yth. Bapak/Ibu,</p><p>Berikut kami lampirkan dokumen <strong>Invoice</strong> beserta <strong>Bukti Acara Serah Terima (BAPP)</strong> yang telah ditandatangani untuk proses penagihan.</p><p>Terima kasih atas kerja sama Anda.</p>',
        ]);
    }

    public function getTitle(): string
    {
        return 'Send Invoice Email';
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function getContactOptions(): array
    {
        $contacts = $this->record->customer?->contacts ?? [];

        return collect($contacts)->mapWithKeys(function ($contact) {
            $value = ($contact['email'] ?? $contact['name']).'|'.($contact['name'] ?? '');
            $label = $contact['name'].($contact['email'] ? " ({$contact['email']})" : '');

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
                        if ($state) {
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
                ->label('Back to Invoice')
                ->color('gray')
                ->url(fn () => $this->getResource()::getUrl('view', ['record' => $this->record])),
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

        try {
            // 1. Generate PDF on the fly
            $pdf = Pdf::loadView('finance::pdf.invoice', ['record' => $this->record]);
            $filename = 'invoice-' . str_replace(['/', '\\'], '-', $this->record->invoice_number) . '.pdf';

            // 2. Store temporarily on S3 to get a URL
            $tempPath = "temp/invoices/{$filename}";
            Storage::disk('s3')->put($tempPath, $pdf->output(), 'private');

            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('s3');
            $attachmentUrl = $disk->temporaryUrl($tempPath, now()->addMinutes(60));
            $attachmentName = $filename;

            // 3. Prepare Attachments
            $attachments = [
                [
                    'name' => $attachmentName,
                    'url' => $attachmentUrl,
                ],
            ];

            // Attach BAPP (signed_report) if available
            $bappMedia = $this->record->workCompletionReport?->getFirstMedia('signed_report');
            if ($bappMedia) {
                $bappAttachmentUrl = $bappMedia->disk === 's3' 
                    ? Storage::disk('s3')->temporaryUrl($bappMedia->getPath(), now()->addMinutes(60)) 
                    : url($bappMedia->getUrl());
                
                $attachments[] = [
                    'name' => $bappMedia->file_name,
                    'url' => $bappAttachmentUrl,
                ];
            }

            // 4. Send via External API
            Log::info('Invoice Email Sending Attempt', [
                'invoice_id' => $this->record->id,
                'invoice_number' => $this->record->invoice_number,
                'recipient' => $formData['recipient_email'],
                'attachments_count' => count($attachments),
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
                    'body' => $formData['message'] ?? '',
                    'attachments' => $attachments,
                ]);

            if (!$response->successful()) {
                $errorMsg = $response->json('message') ?? $response->status();

                Log::error('Invoice Email Sending Failed', [
                    'invoice_id' => $this->record->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \Exception('Email system error: ' . $errorMsg);
            }

            // 4. Update Invoice status to Sent
            $this->record->update([
                'status' => InvoiceStatus::Sent,
            ]);

            // 5. Create Communication Log
            $this->record->communicationLogs()->create([
                'recipient_email' => $formData['recipient_email'],
                'sender_id' => auth()->id(),
                'sender_email' => auth()->user()?->email,
                'subject' => $formData['subject'],
                'message' => $formData['message'] ?? '',
                'sent_at' => now(),
            ]);

            Notification::make()
                ->title('Email Sent Successfully')
                ->body('The invoice has been successfully sent to the customer.')
                ->success()
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
        } catch (\Exception $e) {
            Log::error('Invoice Email Failure: ' . $e->getMessage(), [
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
