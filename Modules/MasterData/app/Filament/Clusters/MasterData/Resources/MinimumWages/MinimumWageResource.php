<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\MinimumWages;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\MinimumWages\Pages\CreateMinimumWage;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\MinimumWages\Pages\EditMinimumWage;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\MinimumWages\Pages\ListMinimumWages;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\MinimumWages\Pages\ViewMinimumWage;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\MinimumWages\Schemas\MinimumWageForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\MinimumWages\Tables\MinimumWagesTable;
use Modules\MasterData\Models\MinimumWage;

class MinimumWageResource extends Resource
{
    protected static ?string $model = MinimumWage::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $navigationLabel = 'Minimum Wage';

    protected static ?string $modelLabel = 'Minimum Wage';

    protected static ?string $pluralModelLabel = 'Minimum Wages';

    protected static ?int $navigationSort = 103;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Payroll & Benefits';

    public static function form(Schema $schema): Schema
    {
        return MinimumWageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MinimumWagesTable::configure($table);
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
            'index' => ListMinimumWages::route('/'),
            'create' => CreateMinimumWage::route('/create'),
            'view' => ViewMinimumWage::route('/{record}'),
            'edit' => EditMinimumWage::route('/{record}/edit'),
        ];
    }
}
