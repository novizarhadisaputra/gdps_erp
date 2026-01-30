<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityThresholds\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityThresholds\ProfitabilityThresholdResource;

class CreateProfitabilityThreshold extends CreateRecord
{
    protected static string $resource = ProfitabilityThresholdResource::class;
}
