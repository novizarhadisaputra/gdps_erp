<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PtkpConfigs;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PtkpConfigs\Pages\CreatePtkpConfig;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PtkpConfigs\Pages\EditPtkpConfig;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PtkpConfigs\Pages\ListPtkpConfigs;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PtkpConfigs\Schemas\PtkpConfigForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PtkpConfigs\Tables\PtkpConfigsTable;
use Modules\MasterData\Models\PtkpConfig;

class PtkpConfigResource extends Resource
{
    protected static ?string $model = PtkpConfig::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PtkpConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PtkpConfigsTable::configure($table);
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
            'index' => ListPtkpConfigs::route('/'),
            'create' => CreatePtkpConfig::route('/create'),
            'edit' => EditPtkpConfig::route('/{record}/edit'),
        ];
    }
}
