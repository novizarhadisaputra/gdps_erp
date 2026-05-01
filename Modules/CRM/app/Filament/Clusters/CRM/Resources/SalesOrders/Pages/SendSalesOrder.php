<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages;

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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\CRM\Enums\SalesOrderStatus;
use Modules\CRM\Enums\SalesOrderType;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\SalesOrderResource;

class SendSalesOrder extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SalesOrderResource::class;

    protected string $view = 'crm::filament.clusters.crm.resources.sales-orders.pages.send-sales-order';

    public ?array $data = [];

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        if ($this->record->type === SalesOrderType::Internal) {
            Notification::make()
                ->title('Email Action Not Required')
                ->body('Internal Sales Orders do not require email dispatch to customers.')
                ->warning()
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));

            return;
        }

        $this->form->fill([
            'subject' => 'Sales Order - '.$this->record->number,
            'recipient_email' => $this->record->customer?->email,
            'recipient_name' => $this->record->customer?->name,
            'message' => '<p>A new Sales Order has been generated for your review.</p><p>Please find the details and contact our representative for any further actions.</p>',
        ]);
    }

    public function getTitle(): string
    {
        return 'Send Sales Order Email';
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
                ->label('Back to Sales Order')
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
            // 1. Prepare Attachment
            $attachmentUrl = null;
            $attachmentName = null;

            if ($media = $this->record->getFirstMedia('draft_so')) {
                $attachmentUrl = $media->getTemporaryUrl(now()->addMinutes(60));
                $attachmentName = $media->file_name;
            } elseif ($this->record->type->value === 'external' && $this->record->proposal && $media = $this->record->proposal->getFirstMedia('signed_proposal')) {
                $attachmentUrl = $media->getTemporaryUrl(now()->addMinutes(60));
                $attachmentName = $media->file_name;
            } else {
                // Generate PDF on the fly
                $pdf = Pdf::loadView('crm::pdf.sales-order', ['record' => $this->record]);
                $filename = 'SO-'.str_replace(['/', '\\'], '-', $this->record->number).'.pdf';

                // Store temporarily on S3 to get a URL
                $tempPath = "temp/sales-orders/{$filename}";
                Storage::disk('s3')->put($tempPath, $pdf->output(), 'private');

                $attachmentUrl = Storage::disk('s3')->temporaryUrl($tempPath, now()->addMinutes(60));
                $attachmentName = $filename;
            }

            // 2. Prepare Message
            $messageBody = view('emails.unified', [
                'body' => $formData['message'] ?? '',
                'subject' => $formData['subject'],
            ])->render();

            // 3. Log sending attempt
            Log::info('Sales Order Email Sending Attempt', [
                'so_id' => $this->record->id,
                'number' => $this->record->number,
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
                    'attachments' => [
                        [
                            'name' => $attachmentName,
                            'url' => $attachmentUrl,
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('Sales Order Email Sending Failed', [
                    'so_id' => $this->record->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new Exception('External API Error: '.$response->status());
            }

            // 4. Record Communication Log
            $this->record->communicationLogs()->create([
                'recipient_email' => $formData['recipient_email'],
                'subject' => $formData['subject'],
                'message' => $messageBody,
                'sender_id' => auth()->id(),
                'sent_at' => now(),
            ]);

            // 5. Update Sales Order status to Sent
            $this->record->update([
                'status' => SalesOrderStatus::Submitted,
            ]);

            Notification::make()
                ->title('Email Sent Successfully')
                ->success()
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to Send Email')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
