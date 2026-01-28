<?php

namespace Modules\MasterData\Filament\Resources\UnitsOfMeasure;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Resources\UnitsOfMeasure\Pages\ListUnitsOfMeasure;
use Modules\MasterData\Filament\Resources\UnitsOfMeasure\Schemas\UnitOfMeasureForm;
use Modules\MasterData\Filament\Resources\UnitsOfMeasure\Tables\UnitsOfMeasureTable;
use Modules\MasterData\Models\UnitOfMeasure;

class UnitOfMeasureResource extends Resource
{
    protected static ?string $cluster = \Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster::class;

    protected static ?string $model = UnitOfMeasure::class;

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Product & Inventory';

    public static function form(Schema $schema): Schema
    {
        return UnitOfMeasureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnitsOfMeasureTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnitsOfMeasure::route('/'),
        ];
    }
}
