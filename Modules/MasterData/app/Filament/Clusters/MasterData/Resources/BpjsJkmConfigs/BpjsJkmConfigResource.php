<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkmConfigs;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkmConfigs\Schemas\BpjsJkmConfigForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkmConfigs\Tables\BpjsJkmConfigsTable;
use Modules\MasterData\Models\BpjsJkmConfig;

class BpjsJkmConfigResource extends Resource
{
    protected static ?string $model = BpjsJkmConfig::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $modelLabel = 'JKM Config';

    protected static ?string $pluralModelLabel = 'JKM Configs';

    protected static string|\UnitEnum|null $navigationGroup = 'BPJS & Insurance';

    public static function form(Schema $schema): Schema
    {
        return $schema->components(BpjsJkmConfigForm::schema());
    }

    public static function table(Table $table): Table
    {
        return BpjsJkmConfigsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBpjsJkmConfigs::route('/'),
            'create' => Pages\CreateBpjsJkmConfig::route('/create'),
            'edit' => Pages\EditBpjsJkmConfig::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Bpjs Jkm Config');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Bpjs Jkm Configs');
    }

    public static function getNavigationLabel(): string
    {
        return __('Bpjs Jkm Configs');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('BPJS & Insurance');
    }
}
