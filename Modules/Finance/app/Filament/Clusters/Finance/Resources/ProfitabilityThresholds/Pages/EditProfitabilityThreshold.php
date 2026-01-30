<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityThresholds\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityThresholds\ProfitabilityThresholdResource;

class EditProfitabilityThreshold extends EditRecord
{
    protected static string $resource = ProfitabilityThresholdResource::class;
}
