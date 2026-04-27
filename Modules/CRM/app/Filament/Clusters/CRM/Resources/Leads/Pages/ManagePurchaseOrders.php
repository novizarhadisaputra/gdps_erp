<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\PurchaseOrder\PurchaseOrderResource;

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
                \Filament\Actions\CreateAction::make()
                    ->url(fn () => PurchaseOrderResource::getUrl('create', ['lead' => $this->getOwnerRecord()->id])),
            ]);
    }
}
