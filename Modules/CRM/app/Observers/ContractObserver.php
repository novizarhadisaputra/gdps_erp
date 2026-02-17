<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Enums\ContractType;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Contract;

class ContractObserver
{
    /**
     * Handle the Contract "created" event.
     */
    public function created(Contract $contract): void
    {
        // Auto-link to SalesPlan
        if ($contract->proposal_id && $contract->proposal && $contract->proposal->lead && $contract->proposal->lead->salesPlan) {
            $salesPlan = $contract->proposal->lead->salesPlan;

            if ($contract->type === ContractType::WorkOrder) {
                $salesPlan->update(['work_order_id' => $contract->id]);
            } elseif ($contract->type === ContractType::Agreement) {
                $salesPlan->update(['agreement_id' => $contract->id]);
            }
        }

        // Check if contract is linked to a proposal, and that proposal is linked to a lead
        if ($contract->proposal_id && $contract->proposal && $contract->proposal->lead) {
            $contract->proposal->lead->update([
                'status' => LeadStatus::Negotiation,
            ]);
        }
    }

    /**
     * Handle the Contract "updated" event.
     */
    public function updated(Contract $contract): void
    {
        if ($contract->wasChanged('status') && $contract->status === \Modules\CRM\Enums\ContractStatus::Active) {
            if ($contract->proposal_id && $contract->proposal && $contract->proposal->lead) {
                $contract->proposal->lead->update([
                    'status' => LeadStatus::Won,
                ]);
            }
        }
    }
}
