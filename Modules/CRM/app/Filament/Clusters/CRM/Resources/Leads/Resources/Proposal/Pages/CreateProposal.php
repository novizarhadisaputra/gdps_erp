<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;

class CreateProposal extends CreateRecord
{
    protected static string $resource = ProposalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $lead = $this->getOwnerRecord();

        if ($lead) {
            $data['customer_id'] = $lead->customer_id;
            $data['work_scheme_id'] = $lead->work_scheme_id;
            $data['amount'] = $data['amount'] ?? $lead->estimated_amount;
        }

        return $data;
    }
}
