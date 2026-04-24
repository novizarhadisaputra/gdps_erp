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

        $latest = Invoice::withTrashed()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $invoice->year = (int) $year;
        $invoice->sequence_number = $sequence;
        $invoice->invoice_number = sprintf('GDPS/UB/INV-%03d/%s', $sequence, $shortYear);
        
        if (empty($invoice->payment_info)) {
            $invoice->payment_info = [
                'account_name' => 'PT. Garuda Daya Pratama Sejahtera',
                'banks' => [
                    ['bank_name' => 'Bank Mandiri', 'account_number' => '155-00-1307311-2', 'currency' => 'IDR'],
                    ['bank_name' => 'BNI', 'account_number' => '7201812017', 'currency' => 'IDR'],
                ],
            ];
        }
    }

    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        // Automatically notify approvers when an invoice is generated
        app(\Modules\MasterData\Services\SignatureService::class)->notifyNextApprovers($invoice);
    }
}
