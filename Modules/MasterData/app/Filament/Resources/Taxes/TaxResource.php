<?php

namespace Modules\MasterData\Filament\Resources\Taxes;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Resources\Taxes\Pages\CreateTax;
use Modules\MasterData\Filament\Resources\Taxes\Pages\EditTax;
use Modules\MasterData\Filament\Resources\Taxes\Pages\ListTaxes;
use Modules\MasterData\Filament\Resources\Taxes\Schemas\TaxForm;
use Modules\MasterData\Filament\Resources\Taxes\Tables\TaxesTable;
use Modules\MasterData\Models\Tax;

class TaxResource extends Resource
{
    protected static ?string $cluster = \Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster::class;

    protected static ?string $model = Tax::class;

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TaxForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxesTable::configure($table);
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
            'index' => ListTaxes::route('/'),
        ];
    }
}
