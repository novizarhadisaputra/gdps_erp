<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\SalesPlanResource;

class CreateSalesPlan extends CreateRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = SalesPlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $lead = $this->parentRecord;

        if ($lead) {
            $data['estimated_value'] = $lead->estimated_amount;
            $data['confidence_level'] = $lead->confidence_level;
            $data['job_positions'] = $lead->job_positions;
            $data['revenue_segment_id'] = $lead->revenue_segment_id;
            $data['product_cluster_id'] = $lead->product_cluster_id;
            $data['project_type_id'] = $lead->project_type_id;
            $data['industrial_sector_id'] = $lead->industrial_sector_id;
            $data['project_area_id'] = $lead->project_area_id;
            $data['start_date'] = $lead->start_date;
            $data['end_date'] = $lead->end_date;
        }

        return $data;
    }
}
