<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\CostingTemplate;

class CostingTemplateObserver
{
    /**
     * Handle the CostingTemplate "creating" event.
     */
    public function creating(CostingTemplate $costingTemplate): void
    {
        $year = date('Y');
        $shortYear = date('y');

        $latest = CostingTemplate::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $costingTemplate->year = $year;
        $costingTemplate->sequence_number = $sequence;
        $costingTemplate->code = sprintf('GDPS/UB/TE-%03d/%s', $sequence, $shortYear);

        // Naming convention: Customer Name + Tools & Equipment
        if (! $costingTemplate->name || $costingTemplate->name === 'New Template') {
            $customerName = $costingTemplate->lead?->customer?->name ?? 'Unknown Customer';
            $costingTemplate->name = $customerName.' Tools & Equipment';
        }
    }
}
