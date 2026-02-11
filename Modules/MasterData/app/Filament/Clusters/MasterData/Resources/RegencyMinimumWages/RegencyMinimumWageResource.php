<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages\Pages\ListRegencyMinimumWages;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages\Schemas\RegencyMinimumWageForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages\Tables\RegencyMinimumWagesTable;
use Modules\MasterData\Models\RegencyMinimumWage;

class RegencyMinimumWageResource extends Resource
{
    protected static ?string $model = RegencyMinimumWage::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $navigationLabel = 'Minimum Wage';

    protected static ?string $modelLabel = 'Minimum Wage';

    protected static ?string $pluralModelLabel = 'Minimum Wages';

    protected static ?int $navigationSort = 103;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Human Resources';

    public static function form(Schema $schema): Schema
    {
        return RegencyMinimumWageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegencyMinimumWagesTable::configure($table);
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
            'index' => ListRegencyMinimumWages::route('/'),
        ];
    }
}
