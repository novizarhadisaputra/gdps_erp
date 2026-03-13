<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages\CreateSalesOrder;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages\EditSalesOrder;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages\ListSalesOrders;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Schemas\SalesOrderForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Tables\SalesOrdersTable;
use Modules\CRM\Models\SalesOrder;

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = CRMCluster::class;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return SalesOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AmendmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesOrders::route('/'),
            'create' => CreateSalesOrder::route('/create'),
            'edit' => EditSalesOrder::route('/{record}/edit'),
        ];
    }
}
