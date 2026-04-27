<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\PurchaseOrders;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Resources\PurchaseOrders\Pages\CreatePurchaseOrder;
use Modules\CRM\Filament\Clusters\CRM\Resources\PurchaseOrders\Pages\EditPurchaseOrder;
use Modules\CRM\Filament\Clusters\CRM\Resources\PurchaseOrders\Pages\ListPurchaseOrders;
use Modules\CRM\Filament\Clusters\CRM\Resources\PurchaseOrders\Pages\ViewPurchaseOrder;
use Modules\CRM\Filament\Clusters\CRM\Resources\PurchaseOrders\Schemas\PurchaseOrderForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\PurchaseOrders\Tables\PurchaseOrdersTable;
use Modules\CRM\Models\PurchaseOrder;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $cluster = CRMCluster::class;

    protected static ?int $navigationSort = 4;

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
            'index' => ListPurchaseOrders::route('/'),
            'create' => CreatePurchaseOrder::route('/create'),
            'view' => ViewPurchaseOrder::route('/{record}'),
            'edit' => EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
