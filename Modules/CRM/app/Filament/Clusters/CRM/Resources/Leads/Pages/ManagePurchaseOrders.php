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
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\PurchaseOrder\PurchaseOrderResource;
use Modules\CRM\Models\PurchaseOrder;

class ManagePurchaseOrders extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'purchaseOrders';

    protected static ?string $relatedResource = PurchaseOrderResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    public function getSubheading(): ?string
    {
        return 'Manage Purchase Orders (PO) for this lead.';
    }

    protected static ?string $title = 'Purchase Orders';

    public function form(Schema $schema): Schema
    {
        return PurchaseOrderResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return PurchaseOrderResource::table($table)
            ->headerActions([
                Action::make(__('bookingCode'))
                    ->label(__('Booking PO Code'))
                    ->icon(Heroicon::OutlinedDocumentPlus)
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading(__('Booking Purchase Order Code'))
                    ->modalDescription(__('This will generate a new PO record with an auto-generated code. You can upload the documentation later.'))
                    ->action(function () {
                        $lead = $this->getOwnerRecord();

                        PurchaseOrder::create([
                            'lead_id' => $lead->id,
                            'customer_id' => $lead->customer_id,
                            'proposal_id' => $lead->proposals()->latest()->first()?->id,
                            'order_date' => now(),
                            'status' => ProposalStatus::Draft,
                            'is_manual' => true,
                        ]);

                        Notification::make()
                            ->title(__('PO code booked successfully'))
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
