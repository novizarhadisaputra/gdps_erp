<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters;

use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Pages\ListProductClusters;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Schemas\ProductClusterForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Tables\ProductClustersTable;
use Modules\MasterData\Models\ProductCluster;

class ProductClusterResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = ProductCluster::class;

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Project Structure';

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
