<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\ManpowerTemplate;

class ManpowerTemplateObserver
{
    /**
     * Handle the ManpowerTemplate "creating" event.
     */
    public function creating(ManpowerTemplate $manpowerTemplate): void
    {
        $year = date('Y');
        $shortYear = date('y');

        $latest = ManpowerTemplate::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $manpowerTemplate->year = $year;
        $manpowerTemplate->sequence_number = $sequence;
        $manpowerTemplate->code = sprintf('GDPS/UB/MP-%03d/%s', $sequence, $shortYear);

        // Naming convention: Customer Name + Manpower
        if (! $manpowerTemplate->name || $manpowerTemplate->name === 'New Template') {
            $customerName = $manpowerTemplate->lead?->customer?->name ?? 'Unknown Customer';
            $manpowerTemplate->name = $customerName.' Manpower';
        }
    }
}
