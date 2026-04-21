<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisMonthly\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisMonthly\ProfitabilityAnalysisMonthlyResource;

class EditProfitabilityAnalysisMonthly extends EditRecord
{
    protected static string $resource = ProfitabilityAnalysisMonthlyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();
        $record = $this->getRecord();
        $pa = $record->profitabilityAnalysis;
        $lead = $pa?->lead;

        $leadResource = \Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource::class;
        $paResource = \Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource::class;

        return [
            $leadResource::getUrl() => $leadResource::getBreadcrumb(),
            $leadResource::getUrl('view', ['record' => $lead]) => $lead?->title ?? 'Lead',
            $paResource::getUrl('index', ['lead' => $lead]) => $paResource::getBreadcrumb(),
            $paResource::getUrl('view', ['lead' => $lead, 'record' => $pa]) => $pa?->document_number ?? 'PA',
            $resource::getUrl('index', ['lead' => $lead, 'profitability_analysi' => $pa]) => $resource::getBreadcrumb(),
            '#' => "{$record->month} {$record->year}",
        ];
    }
}
