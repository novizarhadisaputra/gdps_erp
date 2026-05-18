<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkkConfigs;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkkConfigs\Schemas\BpjsJkkConfigForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkkConfigs\Tables\BpjsJkkConfigsTable;
use Modules\MasterData\Models\BpjsJkkConfig;

class BpjsJkkConfigResource extends Resource
{
    protected static ?string $model = BpjsJkkConfig::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $modelLabel = 'JKK Config';

    protected static ?string $pluralModelLabel = 'JKK Configs';

    protected static string|\UnitEnum|null $navigationGroup = 'BPJS & Insurance';

    public static function form(Schema $schema): Schema
    {
        return $schema->components(BpjsJkkConfigForm::schema());
    }

    public static function table(Table $table): Table
    {
        return BpjsJkkConfigsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBpjsJkkConfigs::route('/'),
            'create' => Pages\CreateBpjsJkkConfig::route('/create'),
            'edit' => Pages\EditBpjsJkkConfig::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Bpjs Jkk Config');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Bpjs Jkk Configs');
    }

    public static function getNavigationLabel(): string
    {
        return __('Bpjs Jkk Configs');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('BPJS & Insurance');
    }
}
