<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Logistics\Filament\Clusters\Logistics\LogisticsCluster;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse\Schemas\WarehouseForm;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse\Tables\WarehousesTable;
use Modules\Logistics\Models\Warehouse;

class WarehouseResource extends Resource
{
    protected static ?string $cluster = LogisticsCluster::class;

    protected static ?string $slug = 'warehouses';

    protected static ?string $model = Warehouse::class;

    protected static ?int $navigationSort = 3;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedHome;

    public static function form(Schema $schema): Schema
    {
        return WarehouseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WarehousesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => \Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse\Pages\ListWarehouses::route('/'),
            'create' => \Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse\Pages\CreateWarehouse::route('/create'),
            'view' => \Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse\Pages\ViewWarehouse::route('/{record}'),
            'edit' => \Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse\Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}
