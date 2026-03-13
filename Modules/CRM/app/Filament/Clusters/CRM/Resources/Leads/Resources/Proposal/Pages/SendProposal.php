<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Mail;
use Modules\CRM\app\Emails\ProposalMail;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;

class SendProposal extends ViewRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProposalResource::class;

    protected static string $parentResource = LeadResource::class;

    protected string $view = 'crm::filament.clusters.crm.resources.leads.resources.proposal.pages.send-proposal';

    public ?array $data = [];

    public function mount($record): void
    {
        parent::mount($record);

        $this->form->fill([
            'subject' => 'Proposal - '.$this->record->proposal_number,
            'recipient_email' => $this->record->customer?->email,
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

    public function form(Schema $schema): Schema
    {
        $contacts = $this->record->customer?->contacts ?? [];
        $contactOptions = collect($contacts)->mapWithKeys(function ($contact) {
            $label = $contact['name'].($contact['email'] ? " ({$contact['email']})" : '');

            return [$contact['email'] ?? $contact['name'] => $label];
        })->toArray();

        return $schema
            ->schema([
                Select::make('recipient_contact')
                    ->label('Select Contact')
                    ->options($contactOptions)
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('recipient_email', $state);
                        }
                    }),
                TextInput::make('recipient_email')
                    ->label('Recipient Email')
                    ->email()
                    ->required()
                    ->live(),
                TextInput::make('subject')
                    ->required()
                    ->live(),
                Textarea::make('message')
                    ->rows(5)
                    ->live(),
            ])
            ->statePath('data');
    }

    public function sendEmail(): void
    {
        $formData = $this->form->getState();

        try {
            Mail::to($formData['recipient_email'])
                ->send(new ProposalMail($this->record, $formData['message'] ?? ''));

            Notification::make()
                ->title('Email Sent Successfully')
                ->success()
                ->send();

            $this->redirect($this->getResource()::getUrl('view', [
                'record' => $this->record,
                'lead' => $this->record->lead_id,
            ]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to Send Email')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to Proposal')
                ->color('gray')
                ->url(fn () => $this->getResource()::getUrl('view', [
                    'record' => $this->record,
                    'lead' => $this->record->lead_id,
                ])),
        ];
    }
}
