<?php

namespace Modules\CRM\Observers;

use Filament\Notifications\Notification;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Models\ProfitabilityAnalysis;

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

    /**
     * Handle the ManpowerTemplate "updating" event.
     */
    public function updating(ManpowerTemplate $manpowerTemplate): void
    {
        // We allow updates because the PA uses a snapshot,
        // but we might want to warn the user in the UI (handled by Filament).
        // For now, no hard block on updating.
    }

    /**
     * Handle the ManpowerTemplate "deleting" event.
     */
    public function deleting(ManpowerTemplate $manpowerTemplate): void
    {
        $isUsedInApprovedPa = ProfitabilityAnalysis::query()
            ->where('status', ProfitabilityAnalysisStatus::Approved)
            ->whereJsonContains('analysis_details->manpower_template_id', $manpowerTemplate->id)
            ->exists();

        if ($isUsedInApprovedPa) {
            Notification::make()
                ->title('Cannot Delete Template')
                ->body('This template is currently linked to an Approved Profitability Analysis.')
                ->danger()
                ->send();

            // Returning false in a deleting event prevents the deletion
            throw new \Exception('This template is linked to an Approved Profitability Analysis and cannot be deleted.');
        }
    }
}
