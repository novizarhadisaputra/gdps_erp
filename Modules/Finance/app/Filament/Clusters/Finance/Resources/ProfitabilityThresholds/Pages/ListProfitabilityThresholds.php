<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityThresholds\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityThresholds\ProfitabilityThresholdResource;

class ListProfitabilityThresholds extends ListRecords
{
    protected static string $resource = ProfitabilityThresholdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
