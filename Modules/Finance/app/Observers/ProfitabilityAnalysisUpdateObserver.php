<?php

namespace Modules\Finance\Observers;

use Modules\Finance\Models\ProfitabilityAnalysisUpdate;
use Modules\Finance\Models\ProfitabilityAnalysisActual;

class ProfitabilityAnalysisUpdateObserver
{
    /**
     * Handle the ProfitabilityAnalysisUpdate "saved" event.
     */
    public function saved(ProfitabilityAnalysisUpdate $update): void
    {
        $this->syncToActual($update);
    }

    /**
     * Handle the ProfitabilityAnalysisUpdate "deleted" event.
     */
    public function deleted(ProfitabilityAnalysisUpdate $update): void
    {
        $this->syncToActual($update);
    }

    /**
     * Sync the latest projection to the actual monthly record.
     */
    protected function syncToActual(ProfitabilityAnalysisUpdate $update): void
    {
        if (!$update->profitability_analysis_actual_id) {
            return;
        }

        $actual = ProfitabilityAnalysisActual::find($update->profitability_analysis_actual_id);
        
        if (!$actual) {
            return;
        }

        // Ambil proyeksi terbaru berdasarkan minggu tertinggi atau waktu input terbaru
        $latestUpdate = ProfitabilityAnalysisUpdate::where('profitability_analysis_actual_id', $actual->id)
            ->orderBy('week_number', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestUpdate) {
            $actual->update([
                'actual_revenue' => $latestUpdate->projected_revenue
            ]);
        } else {
            // Jika tidak ada data mingguan tersisa, reset revenue menjadi 0 atau tetap
            $actual->update([
                'actual_revenue' => 0
            ]);
        }
    }
}
