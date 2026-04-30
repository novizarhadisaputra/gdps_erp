<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPtkpConfigs;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPtkpConfigs\Pages\CreateTaxPtkpConfig;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPtkpConfigs\Pages\EditTaxPtkpConfig;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPtkpConfigs\Pages\ListTaxPtkpConfigs;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPtkpConfigs\Pages\ViewTaxPtkpConfig;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPtkpConfigs\Schemas\TaxPtkpConfigForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPtkpConfigs\Tables\TaxPtkpConfigsTable;
use Modules\MasterData\Models\TaxPtkpConfig;

class TaxPtkpConfigResource extends Resource
{
    protected static ?string $model = TaxPtkpConfig::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Taxation';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TaxPtkpConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxPtkpConfigsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaxPtkpConfigs::route('/'),
            'create' => CreateTaxPtkpConfig::route('/create'),
            'view' => ViewTaxPtkpConfig::route('/{record}'),
            'edit' => EditTaxPtkpConfig::route('/{record}/edit'),
        ];
    }
}
