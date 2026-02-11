<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\AssetGroups;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\AssetGroups\Pages\ListAssetGroups;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\AssetGroups\Schemas\AssetGroupForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\AssetGroups\Tables\AssetGroupsTable;
use Modules\MasterData\Models\AssetGroup;

class AssetGroupResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = AssetGroup::class;

    protected static ?string $navigationLabel = 'Asset Categories';

    protected static ?string $modelLabel = 'Asset Category';

    protected static ?string $pluralModelLabel = 'Asset Categories';

    protected static ?int $navigationSort = 84;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance & Accounting';

    public static function form(Schema $schema): Schema
    {
        return AssetGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetGroupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssetGroups::route('/'),
        ];
    }
}
