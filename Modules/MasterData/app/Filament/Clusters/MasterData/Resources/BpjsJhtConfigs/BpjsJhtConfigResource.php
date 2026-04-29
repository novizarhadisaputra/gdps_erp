<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJhtConfigs;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJhtConfigs\Schemas\BpjsJhtConfigForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJhtConfigs\Tables\BpjsJhtConfigsTable;
use Modules\MasterData\Models\BpjsJhtConfig;

class BpjsJhtConfigResource extends Resource
{
    protected static ?string $model = BpjsJhtConfig::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $modelLabel = 'JHT Config';

    protected static ?string $pluralModelLabel = 'JHT Configs';

    protected static string|\UnitEnum|null $navigationGroup = 'BPJS & Insurance';

    public static function form(Schema $schema): Schema
    {
        return $schema->components(BpjsJhtConfigForm::schema());
    }

    public static function table(Table $table): Table
    {
        return BpjsJhtConfigsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBpjsJhtConfigs::route('/'),
            'create' => Pages\CreateBpjsJhtConfig::route('/create'),
            'edit' => Pages\EditBpjsJhtConfig::route('/{record}/edit'),
        ];
    }
}
