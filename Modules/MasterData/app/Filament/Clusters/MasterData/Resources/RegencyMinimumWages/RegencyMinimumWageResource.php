<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages\Pages\CreateRegencyMinimumWage;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages\Pages\EditRegencyMinimumWage;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages\Pages\ListRegencyMinimumWages;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages\Schemas\RegencyMinimumWageForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages\Tables\RegencyMinimumWagesTable;
use Modules\MasterData\Models\RegencyMinimumWage;

class RegencyMinimumWageResource extends Resource
{
    protected static ?string $model = RegencyMinimumWage::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
