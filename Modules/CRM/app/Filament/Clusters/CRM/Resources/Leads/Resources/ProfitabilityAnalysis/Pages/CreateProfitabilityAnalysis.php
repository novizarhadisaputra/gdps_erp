<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource;

class CreateProfitabilityAnalysis extends CreateRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProfitabilityAnalysisResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $lead = $this->parentRecord;

        if ($lead) {
            $data['customer_id'] = $lead->customer_id;
            $data['work_scheme_id'] = $lead->work_scheme_id;
            $data['project_area_id'] = $lead->project_area_id;
            $data['product_cluster_id'] = $lead->product_cluster_id;

            // Try to find the latest approved General Information
            $gi = $lead->generalInformations()
                ->where('status', 'approved')
                ->latest()
                ->first();

            if ($gi) {
                $data['general_information_id'] = $gi->id;
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index', ['lead' => $this->parentRecord]);
    }
}
