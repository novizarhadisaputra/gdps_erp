<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityThresholds\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityThresholds\ProfitabilityThresholdResource;

class ListProfitabilityThresholds extends ListRecords
{
    protected static string $resource = ProfitabilityThresholdResource::class;

    public function getSubheading(): ?string
    {
        return 'Configure minimum acceptable profit margins for varied project types.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
