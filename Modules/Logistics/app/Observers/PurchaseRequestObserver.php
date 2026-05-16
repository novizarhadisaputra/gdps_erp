<?php

namespace Modules\Logistics\Observers;

use Carbon\Carbon;
use Modules\Logistics\Models\PurchaseRequest;

class PurchaseRequestObserver
{
    /**
     * Handle the PurchaseRequest "creating" event.
     */
    public function creating(PurchaseRequest $purchaseRequest): void
    {
        if (empty($purchaseRequest->pr_number)) {
            $purchaseRequest->pr_number = $this->generatePrNumber();
        }

        if (empty($purchaseRequest->status)) {
            $purchaseRequest->status = 'draft';
        }

        if (auth()->check()) {
            $purchaseRequest->user_id = auth()->id();
        }
    }

    /**
     * Generate a unique PR number.
     * Format: PR/YYYY/MM/XXXX
     */
    private function generatePrNumber(): string
    {
        $now = Carbon::now();
        $prefix = "PR/{$now->format('Y')}/{$now->format('m')}/";

        $lastPr = PurchaseRequest::where('pr_number', 'like', $prefix.'%')
            ->orderBy('pr_number', 'desc')
            ->first();

        $sequence = 1;
        if ($lastPr) {
            $lastSequence = (int) substr($lastPr->pr_number, -4);
            $sequence = $lastSequence + 1;
        }

        return $prefix.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
