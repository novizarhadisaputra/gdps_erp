<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\GeneralInformationResource;

class CreateGeneralInformation extends CreateRecord
{
    protected static string $resource = GeneralInformationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $lead = $this->getOwnerRecord();

        if ($lead) {
            $data['customer_id'] = $lead->customer_id;
            $data['project_area_id'] = $lead->project_area_id;
            $data['estimated_start_date'] = $lead->start_date;
            $data['estimated_end_date'] = $lead->end_date;
            $data['scope_of_work'] = $lead->title;
            $data['description'] = $lead->description;
        }

        return $data;
    }
}
