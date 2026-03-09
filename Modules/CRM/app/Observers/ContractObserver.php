<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Enums\ContractType;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Contract;

class ContractObserver
{
    /**
     * Handle the Contract "creating" event.
     */
    public function creating(Contract $contract): void
    {
        if (! $contract->lead_id && $contract->proposal_id && $contract->proposal) {
            $contract->lead_id = $contract->proposal->lead_id;
        }

        if (empty($contract->contract_number)) {
            $year = date('Y');
            $shortYear = date('y');

            $latest = Contract::query()
                ->whereYear('created_at', $year)
                ->where('contract_number', 'LIKE', 'GDPS/UB/CON-%')
                ->orderBy('id', 'desc') // Using ID as proxy for sequence if sequence_number isn't present
                ->first();

            // Extract sequence number from last contract number if possible
            $sequence = 1;
            if ($latest && preg_match('/CON-(\d+)\//', $latest->contract_number, $matches)) {
                $sequence = ((int) $matches[1]) + 1;
            }

            $contract->contract_number = sprintf('GDPS/UB/CON-%03d/%s', $sequence, $shortYear);
        }
    }

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
                'status' => LeadStatus::Contract,
            ]);
        }
    }

    /**
     * Handle the Contract "updated" event.
     */
    public function updated(Contract $contract): void
    {
        if ($contract->wasChanged('status') && $contract->status === ContractStatus::Active) {
            if ($contract->proposal_id && $contract->proposal && $contract->proposal->lead) {
                $contract->proposal->lead->update([
                    'status' => LeadStatus::Won,
                ]);
            }
        }
    }
}
