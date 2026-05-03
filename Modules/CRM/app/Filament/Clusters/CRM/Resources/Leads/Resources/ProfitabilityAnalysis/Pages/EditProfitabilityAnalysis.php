<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Traits\HasProfitabilityAnalysisActions;

class EditProfitabilityAnalysis extends EditRecord
{
    use HasProfitabilityAnalysisActions;
    use InteractsWithParentRecord;

    protected static string $resource = ProfitabilityAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...$this->getProfitabilityAnalysisActions(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();
        $record = $this->getRecord();
        $lead = $this->getParentRecord();

        $leadResource = \Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource::class;

        return [
            $leadResource::getUrl() => $leadResource::getBreadcrumb(),
            $leadResource::getUrl('view', ['record' => $lead]) => $lead?->title ?? 'Lead',
            $resource::getUrl('index', ['lead' => $lead]) => $resource::getBreadcrumb(),
            '#' => $record->number ?? 'PA',
        ];
    }
}
