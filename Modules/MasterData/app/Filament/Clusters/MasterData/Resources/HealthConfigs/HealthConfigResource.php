<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\HealthConfigs;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\HealthConfigs\Schemas\HealthConfigForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\HealthConfigs\Tables\HealthConfigsTable;
use Modules\MasterData\Models\HealthConfig;

class HealthConfigResource extends Resource
{
    protected static ?string $model = HealthConfig::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $modelLabel = 'Health (BPJS Kes) Config';

    protected static ?string $pluralModelLabel = 'Health Configs';

    protected static string|\UnitEnum|null $navigationGroup = 'BPJS & Insurance';

    public static function form(Schema $schema): Schema
    {
        return $schema->components(HealthConfigForm::schema());
    }

    public static function table(Table $table): Table
    {
        return HealthConfigsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHealthConfigs::route('/'),
            'create' => Pages\CreateHealthConfig::route('/create'),
            'edit' => Pages\EditHealthConfig::route('/{record}/edit'),
        ];
    }
}
