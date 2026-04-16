<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisActual\ProfitabilityAnalysisActualResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Tables\ProfitabilityAnalysisActualsTable;

class ManageProfitabilityAnalysisActuals extends ManageRelatedRecords
{
    protected static string $resource = ProfitabilityAnalysisResource::class;

    protected static string $relationship = 'actuals';

    protected static ?string $relatedResource = ProfitabilityAnalysisActualResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $title = 'Monthly Actual Performance';

    public static function getNavigationLabel(): string
    {
        return 'Monthly Performance';
    }

    public function getSubheading(): ?string
    {
        return 'Track actual monthly expenses and revenue for comparison with the plan.';
    }

    public function table(Table $table): Table
    {
        return ProfitabilityAnalysisActualsTable::configure($table)
            ->headerActions([
                CreateAction::make()
                    ->label('Add Monthly Record'),
            ]);
    }
}
