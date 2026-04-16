<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\ProfitabilityAnalysisMonthlyResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Resources\ProfitabilityAnalysisWeekly\ProfitabilityAnalysisWeeklyResource;

class ManageWeeklyUpdates extends ManageRelatedRecords
{
    protected static string $resource = ProfitabilityAnalysisMonthlyResource::class;

    protected static string $relationship = 'weeklies';

    protected static ?string $relatedResource = ProfitabilityAnalysisWeeklyResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?string $title = 'Weekly Updates & Achievement';

    public static function getNavigationLabel(): string
    {
        return 'Weekly Updates & Achievement';
    }

    public function table(Table $table): Table
    {
        return ProfitabilityAnalysisWeeklyResource::table($table)
            ->headerActions([
                CreateAction::make()
                    ->label('Add Weekly Record'),
            ]);
    }
}
