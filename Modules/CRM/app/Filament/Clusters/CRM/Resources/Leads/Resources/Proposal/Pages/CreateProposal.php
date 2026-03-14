<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;

class CreateProposal extends CreateRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProposalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $lead = $this->parentRecord;

        if ($lead) {
            $data['lead_id'] = $lead->id;
            $data['customer_id'] = $lead->customer_id;

            // Find latest approved or submitted PA
            $latestPA = $lead->profitabilityAnalyses()
                ->whereIn('status', [
                    \Modules\Finance\Enums\ProfitabilityAnalysisStatus::Approved,
                    \Modules\Finance\Enums\ProfitabilityAnalysisStatus::Submitted,
                ])
                ->latest()
                ->first();

            if ($latestPA) {
                $data['profitability_analysis_id'] = $latestPA->id;
                $data['work_scheme_id'] = $latestPA->work_scheme_id;
            } else {
                $data['work_scheme_id'] = $lead->work_scheme_id;
            }

            $data['amount'] = $data['amount'] ?? $lead->estimated_amount;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord(), 'lead' => $this->parentRecord]);
    }
}
