<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxTerRates;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxTerRates\Pages\ListTaxTerRates;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxTerRates\Schemas\TaxTerRateForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxTerRates\Tables\TaxTerRatesTable;
use Modules\MasterData\Models\TaxTerRate;

class TaxTerRateResource extends Resource
{
    protected static ?string $model = TaxTerRate::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Taxation';

    protected static ?string $navigationLabel = 'PPh 21 TER Rates';

    public static function form(Schema $schema): Schema
    {
        return TaxTerRateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxTerRatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaxTerRates::route('/'),
        ];
    }
}
