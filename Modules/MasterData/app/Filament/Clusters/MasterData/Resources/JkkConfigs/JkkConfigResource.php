<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkConfigs;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkConfigs\Schemas\JkkConfigForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkConfigs\Tables\JkkConfigsTable;
use Modules\MasterData\Models\JkkConfig;

class JkkConfigResource extends Resource
{
    protected static ?string $model = JkkConfig::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $modelLabel = 'JKK Config';

    protected static ?string $pluralModelLabel = 'JKK Configs';

    protected static string|\UnitEnum|null $navigationGroup = 'BPJS & Insurance';

    public static function form(Schema $schema): Schema
    {
        return $schema->components(JkkConfigForm::schema());
    }

    public static function table(Table $table): Table
    {
        return JkkConfigsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJkkConfigs::route('/'),
            'create' => Pages\CreateJkkConfig::route('/create'),
            'edit' => Pages\EditJkkConfig::route('/{record}/edit'),
        ];
    }
}
