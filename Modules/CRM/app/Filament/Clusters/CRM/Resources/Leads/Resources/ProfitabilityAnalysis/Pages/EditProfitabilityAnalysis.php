<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource;

class EditProfitabilityAnalysis extends EditRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProfitabilityAnalysisResource::class;
}
