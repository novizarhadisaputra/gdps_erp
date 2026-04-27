<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CooperationAgreement\CooperationAgreementResource;
use Modules\CRM\Models\CooperationAgreement;

class ManageCooperationAgreements extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'cooperationAgreements';

    protected static ?string $relatedResource = CooperationAgreementResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    public function getSubheading(): ?string
    {
        return 'Manage Cooperation Agreements (PKS) for this lead.';
    }

    protected static ?string $title = 'Cooperation Agreements';

    public function form(Schema $schema): Schema
    {
        return CooperationAgreementResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return CooperationAgreementResource::table($table)
            ->headerActions([
                Action::make('bookingCode')
                    ->label('Booking PKS Code')
                    ->icon(Heroicon::OutlinedDocumentPlus)
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Booking Cooperation Agreement (PKS) Code')
                    ->modalDescription('This will generate a new PKS record with an auto-generated code. You can upload the documentation later.')
                    ->action(function () {
                        $lead = $this->getOwnerRecord();

                        CooperationAgreement::create([
                            'lead_id' => $lead->id,
                            'customer_id' => $lead->customer_id,
                            'proposal_id' => $lead->proposals()->latest()->first()?->id,
                            'agreement_date' => now(),
                            'status' => ProposalStatus::Draft,
                            'is_manual' => true,
                        ]);

                        Notification::make()
                            ->title('PKS code booked successfully')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
