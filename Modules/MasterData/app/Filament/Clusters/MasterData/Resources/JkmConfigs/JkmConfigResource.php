<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmConfigs;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmConfigs\Schemas\JkmConfigForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmConfigs\Tables\JkmConfigsTable;
use Modules\MasterData\Models\JkmConfig;

class JkmConfigResource extends Resource
{
    protected static ?string $model = JkmConfig::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $modelLabel = 'JKM Config';

    protected static ?string $pluralModelLabel = 'JKM Configs';

    protected static string|\UnitEnum|null $navigationGroup = 'BPJS & Insurance';

    public static function form(Schema $schema): Schema
    {
        return $schema->components(JkmConfigForm::schema());
    }

    public static function table(Table $table): Table
    {
        return JkmConfigsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJkmConfigs::route('/'),
            'create' => Pages\CreateJkmConfig::route('/create'),
            'edit' => Pages\EditJkmConfig::route('/{record}/edit'),
        ];
    }
}
