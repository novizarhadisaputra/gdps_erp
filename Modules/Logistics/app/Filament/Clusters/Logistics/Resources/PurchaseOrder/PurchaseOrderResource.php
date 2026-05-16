<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Logistics\Filament\Clusters\Logistics\LogisticsCluster;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\Schemas\PurchaseOrderForm;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\Tables\PurchaseOrdersTable;
use Modules\Logistics\Models\PurchaseOrder;

class PurchaseOrderResource extends Resource
{
    protected static ?string $cluster = LogisticsCluster::class;

    protected static ?string $slug = 'purchase-orders';

    protected static ?string $model = PurchaseOrder::class;

    protected static ?int $navigationSort = 2;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    public static function form(Schema $schema): Schema
    {
        return PurchaseOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseOrdersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => \Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\Pages\ListPurchaseOrders::route('/'),
            'create' => \Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\Pages\CreatePurchaseOrder::route('/create'),
            'view' => \Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\Pages\ViewPurchaseOrder::route('/{record}'),
            'edit' => \Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
