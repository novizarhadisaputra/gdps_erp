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
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\WorkOrder\WorkOrderResource;
use Modules\CRM\Models\WorkOrder;

class ManageWorkOrders extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'workOrders';

    protected static ?string $relatedResource = WorkOrderResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedWrench;

    public function getSubheading(): ?string
    {
        return 'Manage Work Orders (SPK) for this lead.';
    }

    protected static ?string $title = 'Work Orders';

    public function form(Schema $schema): Schema
    {
        return WorkOrderResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return WorkOrderResource::table($table)
            ->headerActions([
                Action::make(__('bookingCode'))
                    ->label(__('Booking SPK Code'))
                    ->icon(Heroicon::OutlinedDocumentPlus)
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading(__('Booking Work Order (SPK) Code'))
                    ->modalDescription(__('This will generate a new SPK record with an auto-generated code. You can upload the documentation later.'))
                    ->action(function () {
                        $lead = $this->getOwnerRecord();

                        WorkOrder::create([
                            'lead_id' => $lead->id,
                            'customer_id' => $lead->customer_id,
                            'proposal_id' => $lead->proposals()->latest()->first()?->id,
                            'order_date' => now(),
                            'status' => ProposalStatus::Draft,
                            'is_manual' => true,
                        ]);

                        Notification::make()
                            ->title(__('SPK code booked successfully'))
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
