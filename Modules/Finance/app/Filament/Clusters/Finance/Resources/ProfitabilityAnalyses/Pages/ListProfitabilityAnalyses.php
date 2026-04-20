<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
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

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->icon(Heroicon::OutlinedListBullet),
            'active' => Tab::make()
                ->icon(Heroicon::OutlinedCalculator)
                ->modifyQueryUsing(fn ($query) => $query->withoutTrashed()),
            'archived' => Tab::make()
                ->label('Trash / Archived')
                ->icon(Heroicon::OutlinedTrash)
                ->modifyQueryUsing(fn ($query) => $query->onlyTrashed()),
        ];
    }
}
