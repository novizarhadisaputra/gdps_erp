<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJpConfigs;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJpConfigs\Schemas\BpjsJpConfigForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJpConfigs\Tables\BpjsJpConfigsTable;
use Modules\MasterData\Models\BpjsJpConfig;

class BpjsJpConfigResource extends Resource
{
    protected static ?string $model = BpjsJpConfig::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $modelLabel = 'JP Config';

    protected static ?string $pluralModelLabel = 'JP Configs';

    protected static string|\UnitEnum|null $navigationGroup = 'BPJS & Insurance';

    public static function form(Schema $schema): Schema
    {
        return $schema->components(BpjsJpConfigForm::schema());
    }

    public static function table(Table $table): Table
    {
        return BpjsJpConfigsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBpjsJpConfigs::route('/'),
            'create' => Pages\CreateBpjsJpConfig::route('/create'),
            'edit' => Pages\EditBpjsJpConfig::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Bpjs Jp Config');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Bpjs Jp Configs');
    }

    public static function getNavigationLabel(): string
    {
        return __('Bpjs Jp Configs');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('BPJS & Insurance');
    }
}
