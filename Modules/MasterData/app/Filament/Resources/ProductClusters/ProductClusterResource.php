<?php

namespace Modules\MasterData\Filament\Resources\ProductClusters;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Resources\ProductClusters\Pages\CreateProductCluster;
use Modules\MasterData\Filament\Resources\ProductClusters\Pages\EditProductCluster;
use Modules\MasterData\Filament\Resources\ProductClusters\Pages\ListProductClusters;
use Modules\MasterData\Filament\Resources\ProductClusters\Schemas\ProductClusterForm;
use Modules\MasterData\Filament\Resources\ProductClusters\Tables\ProductClustersTable;
use Modules\MasterData\Models\ProductCluster;

class ProductClusterResource extends Resource
{
    protected static ?string $cluster = \Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster::class;

    protected static ?string $model = ProductCluster::class;

    protected static ?int $navigationSort = 8;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ProductClusterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductClustersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductClusters::route('/'),
        ];
    }
}
