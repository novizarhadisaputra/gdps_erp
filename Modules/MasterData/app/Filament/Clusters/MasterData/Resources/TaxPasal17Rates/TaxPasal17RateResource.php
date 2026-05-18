<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPasal17Rates;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPasal17Rates\Pages\ListTaxPasal17Rates;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPasal17Rates\Schemas\TaxPasal17RateForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPasal17Rates\Tables\TaxPasal17RatesTable;
use Modules\MasterData\Models\TaxPasal17Rate;

class TaxPasal17RateResource extends Resource
{
    protected static ?string $model = TaxPasal17Rate::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Taxation';

    protected static ?string $navigationLabel = 'Pasal 17 Rates';

    public static function form(Schema $schema): Schema
    {
        return TaxPasal17RateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxPasal17RatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaxPasal17Rates::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Tax Pasal17 Rate');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Tax Pasal17 Rates');
    }

    public static function getNavigationLabel(): string
    {
        return __('Tax Pasal17 Rates');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Taxation');
    }
}
