<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityThresholds;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Finance\Filament\Clusters\Finance\FinanceCluster;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityThresholds\Pages\CreateProfitabilityThreshold;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityThresholds\Pages\EditProfitabilityThreshold;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityThresholds\Pages\ListProfitabilityThresholds;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityThresholds\Schemas\ProfitabilityThresholdForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityThresholds\Tables\ProfitabilityThresholdsTable;
use Modules\Finance\Models\ProfitabilityThreshold;

class ProfitabilityThresholdResource extends Resource
{
    protected static ?string $cluster = FinanceCluster::class;

    protected static ?string $model = ProfitabilityThreshold::class;

    protected static ?int $navigationSort = 10;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return ProfitabilityThresholdForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfitabilityThresholdsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfitabilityThresholds::route('/'),
        ];
    }
}
