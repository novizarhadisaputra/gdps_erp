<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\WorkOrder;

class WorkOrderObserver
{
    public function creating(WorkOrder $workOrder): void
    {
        if (filled($workOrder->number)) {
            return;
        }

        $year = date('Y');
        $shortYear = date('y');

        $latest = WorkOrder::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $workOrder->year = $year;
        $workOrder->sequence_number = $sequence;
        $workOrder->number = sprintf('GDPS/UB/SPK-%03d/%s', $sequence, $shortYear);
    }
}
