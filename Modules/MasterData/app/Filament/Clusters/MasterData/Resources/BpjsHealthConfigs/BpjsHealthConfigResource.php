<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsHealthConfigs;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsHealthConfigs\Schemas\BpjsHealthConfigForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsHealthConfigs\Tables\BpjsHealthConfigsTable;
use Modules\MasterData\Models\BpjsHealthConfig;

class BpjsHealthConfigResource extends Resource
{
    protected static ?string $model = BpjsHealthConfig::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $modelLabel = 'Health (BPJS Kes) Config';

    protected static ?string $pluralModelLabel = 'Health Configs';

    protected static string|\UnitEnum|null $navigationGroup = 'BPJS & Insurance';

    public static function form(Schema $schema): Schema
    {
        return $schema->components(BpjsHealthConfigForm::schema());
    }

    public static function table(Table $table): Table
    {
        return BpjsHealthConfigsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBpjsHealthConfigs::route('/'),
            'create' => Pages\CreateBpjsHealthConfig::route('/create'),
            'edit' => Pages\EditBpjsHealthConfig::route('/{record}/edit'),
        ];
    }
}
