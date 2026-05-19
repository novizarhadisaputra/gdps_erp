<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Enums\MoAStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\MinutesOfAgreementResource;
use Modules\CRM\Models\MinutesOfAgreement;

class ManageMinutesOfAgreements extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'minutesOfAgreements';

    protected static ?string $relatedResource = MinutesOfAgreementResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    public function getSubheading(): ?string
    {
        return 'Manage Minutes of Agreement (BA) for this lead.';
    }

    protected static ?string $title = 'Minutes of Agreement';

    public function form(Schema $schema): Schema
    {
        return MinutesOfAgreementResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return MinutesOfAgreementResource::table($table)
            ->headerActions([
                Action::make(__('bookingCode'))
                    ->label(__('Booking BAK Code'))
                    ->icon(Heroicon::OutlinedDocumentPlus)
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading(__('Booking Minutes of Agreement (BAK) Code'))
                    ->modalDescription(__('This will generate a new BAK record with an auto-generated code. You can upload the documentation later.'))
                    ->action(function () {
                        $lead = $this->getOwnerRecord();

                        MinutesOfAgreement::create([
                            'lead_id' => $lead->id,
                            'customer_id' => $lead->customer_id,
                            'proposal_id' => $lead->proposals()->latest()->first()?->id,
                            'negotiation_date' => now(),
                            'status' => MoAStatus::Draft,
                            'is_manual' => true,
                        ]);

                        Notification::make()
                            ->title(__('BAK code booked successfully'))
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
