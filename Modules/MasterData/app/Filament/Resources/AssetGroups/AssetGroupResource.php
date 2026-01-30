<?php

namespace Modules\MasterData\Filament\Resources\AssetGroups;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Resources\AssetGroups\Pages\CreateAssetGroup;
use Modules\MasterData\Filament\Resources\AssetGroups\Pages\EditAssetGroup;
use Modules\MasterData\Filament\Resources\AssetGroups\Pages\ListAssetGroups;
use Modules\MasterData\Filament\Resources\AssetGroups\Schemas\AssetGroupForm;
use Modules\MasterData\Filament\Resources\AssetGroups\Tables\AssetGroupsTable;
use Modules\MasterData\Models\AssetGroup;

class AssetGroupResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = AssetGroup::class;

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static string|\UnitEnum|null $navigationGroup = 'Financial Settings';

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
            'create' => CreateAssetGroup::route('/create'),
            'edit' => EditAssetGroup::route('/{record}/edit'),
        ];
    }
}
