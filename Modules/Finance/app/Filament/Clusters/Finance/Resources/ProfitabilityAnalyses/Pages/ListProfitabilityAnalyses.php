<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource;

class ListProfitabilityAnalyses extends ListRecords
{
    protected static string $resource = ProfitabilityAnalysisResource::class;

    public function getSubheading(): ?string
    {
        return 'Conduct financial analysis on project profitability and margins.';
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label('Create Manual'),
        ];
    }
}
