<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtConfigs;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtConfigs\Schemas\JhtConfigForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtConfigs\Tables\JhtConfigsTable;
use Modules\MasterData\Models\JhtConfig;

class JhtConfigResource extends Resource
{
    protected static ?string $model = JhtConfig::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $modelLabel = 'JHT Config';

    protected static ?string $pluralModelLabel = 'JHT Configs';

    protected static string|\UnitEnum|null $navigationGroup = 'BPJS & Insurance';

    public static function form(Schema $schema): Schema
    {
        return $schema->components(JhtConfigForm::schema());
    }

    public static function table(Table $table): Table
    {
        return JhtConfigsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJhtConfigs::route('/'),
            'create' => Pages\CreateJhtConfig::route('/create'),
            'edit' => Pages\EditJhtConfig::route('/{record}/edit'),
        ];
    }
}
