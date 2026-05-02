<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Pages;

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
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\AmendmentResource;

class SendAmendment extends Page
{
    use InteractsWithRecord;

    protected static string $resource = AmendmentResource::class;

    protected string $view = 'crm::filament.clusters.crm.resources.sales-orders.resources.amendment.pages.send-amendment';

    public ?array $data = [];

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->form->fill([
            'subject' => 'Sales Order Amendment - '.$this->record->amendment_number,
            'recipient_email' => $this->record->salesOrder->customer?->email,
            'recipient_name' => $this->record->salesOrder->customer?->name,
            'message' => '<p>A new amendment for your Sales Order has been proposed.</p><p>Please find the comparison and revised details attached for your review.</p>',
        ]);
    }

    public function getTitle(): string
    {
        return 'Send Amendment Email';
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function getContactOptions(): array
    {
        $contacts = $this->record->salesOrder->customer?->contacts ?? [];

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
                        $customer = $this->record->salesOrder->customer;
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
                ->label('Back to Amendment')
                ->color('gray')
                ->url(fn () => $this->getResource()::getUrl('view', ['record' => $this->record, 'sales_order' => $this->record->sales_order_id])),
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
            // 1. Prepare Attachment
            $attachmentUrl = null;
            $attachmentName = null;

            if ($media = $this->record->getFirstMedia('signed_soa')) {
                $attachmentUrl = $media->getTemporaryUrl(now()->addMinutes(60));
                $attachmentName = $media->file_name;
            } elseif ($media = $this->record->getFirstMedia('draft_soa')) {
                $attachmentUrl = $media->getTemporaryUrl(now()->addMinutes(60));
                $attachmentName = $media->file_name;
            }

            // 2. Prepare Message
            $messageBody = view('emails.unified', [
                'body' => $formData['message'] ?? '',
                'subject' => $formData['subject'],
            ])->render();

            // 3. Log sending attempt to System Log
            Log::info('Amendment Email Sending Attempt', [
                'amendment_id' => $this->record->id,
                'amendment_number' => $this->record->amendment_number,
                'recipient' => $formData['recipient_email'],
            ]);

            // 4. Send via External API
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
                    'attachments' => $attachmentUrl ? [
                        [
                            'name' => $attachmentName,
                            'url' => $attachmentUrl,
                        ],
                    ] : [],
                ]);

            if (! $response->successful()) {
                throw new \Exception('External API Error: '.$response->status());
            }

            // 4. Record Activity Log for UI Visibility
            if (function_exists('activity')) {
                activity()
                    ->performedOn($this->record)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'to' => $formData['recipient_email'],
                        'subject' => $formData['subject'],
                    ])
                    ->log('Amendment email sent to '.$formData['recipient_email']);
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

            Notification::make()
                ->title('Email Sent Successfully')
                ->success()
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record, 'sales_order' => $this->record->sales_order_id]));
        } catch (\Exception $e) {
            Log::error('Amendment Email Sending Failed', [
                'amendment_id' => $this->record->id,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Failed to Send Email')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
