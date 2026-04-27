<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\SummaryProfitabilityAnalysis as BasePage;

class SummaryProfitabilityAnalysis extends BasePage
{
    use InteractsWithParentRecord;

    protected static string $resource = ProfitabilityAnalysisResource::class;

    protected static string $parentResource = LeadResource::class;

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();
        $record = $this->getRecord();
        $lead = $this->getParentRecord();

        $leadResource = LeadResource::class;

        return [
            $leadResource::getUrl() => $leadResource::getBreadcrumb(),
            $leadResource::getUrl('view', ['record' => $lead]) => $lead?->title ?? 'Lead',
            $resource::getUrl('index', ['lead' => $lead]) => $resource::getBreadcrumb(),
            '#' => $record->number ?? 'PA',
        ];
    }
}
