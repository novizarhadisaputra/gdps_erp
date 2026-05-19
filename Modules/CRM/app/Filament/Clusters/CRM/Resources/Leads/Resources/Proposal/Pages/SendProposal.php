<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;

class SendProposal extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ProposalResource::class;

    protected static string $parentResource = LeadResource::class;

    protected string $view = 'crm::filament.clusters.crm.resources.leads.resources.proposal.pages.send-proposal';

    public ?array $data = [];

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->form->fill([
            'subject' => 'Proposal - '.$this->record->number,
            'recipient_email' => $this->record->customer?->email,
            'recipient_name' => $this->record->customer?->name,
            'message' => '<p>Please find the attached proposal for our services.</p><p>If you have any questions or require further information, please do not hesitate to contact us.</p>',
        ]);
    }

    public function getTitle(): string
    {
        return 'Send Proposal Email';
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
                    ->label(__('Select Contact'))
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
                    ->editOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('email')->email()->required(),
                    ])
                    ->editOptionAction(function (Action $action) {
                        return $action
                            ->fillForm(function (string $state) {
                                $customer = $this->record->customer;
                                $contacts = $customer->contacts ?? [];
                                [$emailPart, $namePart] = explode('|', $state);
                                foreach ($contacts as $contact) {
                                    if (($contact['email'] ?? $contact['name']) === $emailPart) {
                                        return $contact;
                                    }
                                }

                                return [];
                            })
                            ->action(function (array $data, string $state) {
                                $customer = $this->record->customer;
                                $contacts = $customer->contacts ?? [];
                                [$emailPart, $namePart] = explode('|', $state);
                                foreach ($contacts as &$contact) {
                                    if (($contact['email'] ?? $contact['name']) === $emailPart) {
                                        $contact = $data;
                                        break;
                                    }
                                }
                                $customer->update(['contacts' => $contacts]);

                                return ($data['email'] ?? $data['name']).'|'.($data['name'] ?? '');
                            });
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            [$email, $name] = explode('|', $state);
                            $set('recipient_email', $email);
                            $set('recipient_name', $name);
                        }
                    }),
                TextInput::make('recipient_email')
                    ->label(__('Recipient Email'))
                    ->email()
                    ->required()
                    ->live(onBlur: false),
                TextInput::make('subject')
                    ->required()
                    ->live(onBlur: false),
                RichEditor::make('message')
                    ->fileAttachmentsDisk('s3')
                    ->fileAttachmentsDirectory('proposals/attachments')
                    ->fileAttachmentsVisibility('private')
                    ->live(onBlur: false)
                    ->debounce(500),
            ])
            ->statePath('data');
    }

    public function sendEmailAction(): Action
    {
        return Action::make(__('sendEmail'))
            ->label(__('Send Email'))
            ->icon('heroicon-o-paper-airplane')
            ->requiresConfirmation()
            ->action(fn () => $this->sendEmail());
    }

    public function sendEmail(): void
    {
        $formData = $this->form->getState();

        try {
            // 1. Prepare Attachment
            $attachmentUrl = null;
            $attachmentName = null;

            if ($media = $this->record->getFirstMedia('signed_proposal')) {
                $attachmentUrl = $media->getTemporaryUrl(now()->addMinutes(30));
                $attachmentName = $media->file_name;
            } elseif ($media = $this->record->getFirstMedia('final_proposal')) {
                $attachmentUrl = $media->getTemporaryUrl(now()->addMinutes(30));
                $attachmentName = $media->file_name;
            } else {
                // Generate PDF on the fly as fallback
                $pdf = Pdf::loadView('crm::pdf.proposal', ['record' => $this->record]);
                $filename = 'proposal-'.str_replace(['/', '\\'], '-', $this->record->number).'.pdf';

                // Store temporarily on S3 to get a URL
                $tempPath = "temp/proposals/{$filename}";
                Storage::disk('s3')->put($tempPath, $pdf->output(), 'private');

                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                $disk = Storage::disk('s3');
                $attachmentUrl = $disk->temporaryUrl($tempPath, now()->addMinutes(60));
                $attachmentName = $filename;
            }

            // 3. Prepare Message
            $messageBody = view('emails.unified', [
                'body' => $formData['message'] ?? '',
                'subject' => $formData['subject'],
            ])->render();

            // 4. Send via External API
            Log::info('Proposal Email Sending Attempt', [
                'proposal_id' => $this->record->id,
                'proposal_number' => $this->record->number,
                'recipient' => $formData['recipient_email'],
            ]);

            $response = Http::timeout(60) // Add 60s timeout
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

                Log::error('Proposal Email Sending Failed', [
                    'proposal_id' => $this->record->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \Exception('Email system error: '.$errorMsg);
            }

            // Record Activity Log
            if (function_exists('activity')) {
                activity()
                    ->performedOn($this->record)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'to' => $formData['recipient_email'],
                        'subject' => $formData['subject'],
                    ])
                    ->log('Proposal email sent to '.$formData['recipient_email']);
            }

            // 5. Create Communication Log
            $this->record->communicationLogs()->create([
                'recipient_email' => $formData['recipient_email'],
                'sender_id' => auth()->id(),
                'sender_email' => auth()->user()?->email,
                'subject' => $formData['subject'],
                'message' => $messageBody,
                'sent_at' => now(),
            ]);

            // 6. Update Proposal status to Sent
            $this->record->update([
                'status' => ProposalStatus::Sent,
            ]);

            Notification::make()
                ->title(__('Email Sent Successfully'))
                ->body('Proposal sent via External API and status updated.')
                ->success()
                ->send();

            $this->redirect(route('filament.admin.crm.resources.leads.proposals.view', [
                'record' => $this->record->id,
                'lead' => $this->record->lead_id,
            ]));
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Failed to Send Email'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make(__('back'))
                ->label(__('Back to Proposal'))
                ->color('gray')
                ->url(fn () => route('filament.admin.crm.resources.leads.proposals.view', [
                    'record' => $this->record->id,
                    'lead' => $this->record->lead_id,
                ])),
        ];
    }
}
