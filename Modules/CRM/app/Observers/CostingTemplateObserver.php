<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\CostingTemplate;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Filament\Notifications\Notification;

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

    /**
     * Handle the CostingTemplate "updating" event.
     */
    public function updating(CostingTemplate $costingTemplate): void
    {
        // Snapshot logic in PA handles updates
    }

    /**
     * Handle the CostingTemplate "deleting" event.
     */
    public function deleting(CostingTemplate $costingTemplate): void
    {
        $isUsedInApprovedPa = ProfitabilityAnalysis::query()
            ->where('status', ProfitabilityAnalysisStatus::Approved)
            ->whereJsonContains('analysis_details->costing_template_id', $costingTemplate->id)
            ->exists();

        if ($isUsedInApprovedPa) {
            Notification::make()
                ->title('Cannot Delete Template')
                ->body('This template is currently linked to an Approved Profitability Analysis.')
                ->danger()
                ->send();

            throw new \Exception('This template is linked to an Approved Profitability Analysis and cannot be deleted.');
        }
    }
}
