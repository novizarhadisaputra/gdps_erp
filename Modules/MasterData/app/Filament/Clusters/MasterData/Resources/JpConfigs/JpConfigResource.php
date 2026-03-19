<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JpConfigs;

use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JpConfigs\Schemas\JpConfigForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JpConfigs\Tables\JpConfigsTable;
use Modules\MasterData\Models\JpConfig;

class JpConfigResource extends Resource
{
    protected static ?string $model = JpConfig::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $modelLabel = 'JP Config';

    protected static ?string $pluralModelLabel = 'JP Configs';

    protected static string|\UnitEnum|null $navigationGroup = 'BPJS & Insurance';

    public static function form(Schema $schema): Schema
    {
        return $schema->components(JpConfigForm::schema());
    }

    public static function table(Table $table): Table
    {
        return JpConfigsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJpConfigs::route('/'),
            'create' => Pages\CreateJpConfig::route('/create'),
            'edit' => Pages\EditJpConfig::route('/{record}/edit'),
        ];
    }
}
