<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsConfigs;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsConfigs\Pages\CreateBpjsConfig;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsConfigs\Pages\EditBpjsConfig;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsConfigs\Pages\ListBpjsConfigs;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsConfigs\Schemas\BpjsConfigForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsConfigs\Tables\BpjsConfigsTable;
use Modules\MasterData\Models\BpjsConfig;

class BpjsConfigResource extends Resource
{
    protected static ?string $model = BpjsConfig::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Human Resources';

    public static function form(Schema $schema): Schema
    {
        return BpjsConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BpjsConfigsTable::configure($table);
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
            'index' => ListBpjsConfigs::route('/'),
        ];
    }
}
