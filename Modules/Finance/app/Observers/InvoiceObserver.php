<?php

namespace Modules\Finance\Observers;

use Modules\Finance\Models\Invoice;

class InvoiceObserver
{
    /**
     * Handle the Invoice "creating" event.
     */
    public function creating(Invoice $invoice): void
    {
        if (filled($invoice->invoice_number) && $invoice->invoice_number !== 'Auto-generated') {
            return;
        }

        $year = date('Y');
        $shortYear = date('y');

        $latest = Invoice::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $invoice->year = (int) $year;
        $invoice->sequence_number = $sequence;
        $invoice->invoice_number = sprintf('GDPS/UB/INV-%03d/%s', $sequence, $shortYear);
    }
}
