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
use Illuminate\Support\Facades\Mail;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\InvoiceResource;
use Modules\Finance\Mail\InvoiceMail;

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
            'subject' => 'Invoice '.$this->record->invoice_number,
            'recipient_email' => $this->record->customer?->email,
            'recipient_name' => $this->record->customer?->name,
            'message' => '<p>Please find your invoice attached.</p><p>Thank you for your business!</p>',
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
            Mail::to($formData['recipient_email'])->send(new InvoiceMail(
                $this->record,
                $formData['subject'],
                $formData['message']
            ));

            $this->record->update([
                'status' => InvoiceStatus::Sent,
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
