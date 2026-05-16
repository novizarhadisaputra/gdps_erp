<?php

namespace Modules\Logistics\Observers;

use Carbon\Carbon;
use Modules\Logistics\Models\PurchaseOrder;

class PurchaseOrderObserver
{
    /**
     * Handle the PurchaseOrder "creating" event.
     */
    public function creating(PurchaseOrder $purchaseOrder): void
    {
        if (empty($purchaseOrder->po_number)) {
            $purchaseOrder->po_number = $this->generatePoNumber();
        }

        if (empty($purchaseOrder->status)) {
            $purchaseOrder->status = 'draft';
        }

        if (auth()->check()) {
            $purchaseOrder->user_id = auth()->id();
        }
    }

    /**
     * Handle the PurchaseOrder "updated" event.
     */
    public function updated(PurchaseOrder $purchaseOrder): void
    {
        // Check if status was changed to completed
        if ($purchaseOrder->wasChanged('status') && $purchaseOrder->status === \Modules\Logistics\Enums\PurchaseOrderStatus::Completed) {
            $this->syncStockBalances($purchaseOrder);
        }
    }

    /**
     * Synchronize stock balances based on PO items.
     */
    private function syncStockBalances(PurchaseOrder $purchaseOrder): void
    {
        if (! $purchaseOrder->warehouse_id) {
            return;
        }

        foreach ($purchaseOrder->items as $item) {
            \Modules\Logistics\Models\StockBalance::updateOrCreate(
                [
                    'warehouse_id' => $purchaseOrder->warehouse_id,
                    'item_id' => $item->item_id,
                ],
                [
                    'quantity' => \Illuminate\Support\Facades\DB::raw("quantity + {$item->quantity}"),
                ]
            );
        }
    }

    /**
     * Generate a unique PO number.
     * Format: PO/YYYY/MM/XXXX
     */
    private function generatePoNumber(): string
    {
        $now = Carbon::now();
        $prefix = "PO/{$now->format('Y')}/{$now->format('m')}/";

        $lastPo = PurchaseOrder::where('po_number', 'like', $prefix.'%')
            ->orderBy('po_number', 'desc')
            ->first();

        $sequence = 1;
        if ($lastPo) {
            $lastSequence = (int) substr($lastPo->po_number, -4);
            $sequence = $lastSequence + 1;
        }

        return $prefix.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
