<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\MinutesOfAgreementResource;

class CreateMinutesOfAgreement extends CreateRecord
{
    use \Filament\Resources\Pages\Concerns\InteractsWithParentRecord;

    protected static string $resource = MinutesOfAgreementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $lead = $this->parentRecord;

        if ($lead) {
            $data['lead_id'] = $lead->id;
            $data['customer_id'] = $lead->customer_id;
        }

        return $data;
    }
}
