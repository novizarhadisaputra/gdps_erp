<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisMonthly\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisMonthly\ProfitabilityAnalysisMonthlyResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisMonthly\Resources\ProfitabilityAnalysisWeekly\ProfitabilityAnalysisWeeklyResource;
use Modules\Finance\Enums\ProfitabilityAnalysisMonthlyStatus;

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
                    ->label('Add Weekly Record')
                    ->visible(fn () => $this->getOwnerRecord()->status === ProfitabilityAnalysisMonthlyStatus::Draft),
            ]);
    }
}
