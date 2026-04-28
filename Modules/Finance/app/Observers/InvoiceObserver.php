<?php

namespace Modules\Finance\Observers;

use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\Invoice;
use Modules\MasterData\Services\SignatureService;

class InvoiceObserver
{
    /**
     * Handle the Invoice "creating" event.
     */
    public function creating(Invoice $invoice): void
    {
        if (filled($invoice->number) && $invoice->number !== 'Auto-generated') {
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
        $invoice->number = sprintf('GDPS/UB/INV-%03d/%s', $sequence, $shortYear);

        if (empty($invoice->payment_info)) {
            $bank = null;

            // Check if it's an internal SO
            $isInternal = false;
            $source = $invoice->sourceable;
            if ($source instanceof \Modules\CRM\Models\SalesOrder) {
                $isInternal = $source->type->value === \Modules\CRM\Enums\SalesOrderType::Internal->value;
            } elseif ($source instanceof \Modules\Project\Models\WorkCompletionReport && $source->sourceable instanceof \Modules\CRM\Models\SalesOrder) {
                $isInternal = $source->sourceable->type->value === \Modules\CRM\Enums\SalesOrderType::Internal->value;
            }

            if ($isInternal) {
                $bank = \Modules\MasterData\Models\BankAccount::where('account_name', 'like', '%Internal%')
                    ->where('is_active', true)
                    ->first();
            }

            if (! $bank) {
                $bank = \Modules\MasterData\Models\BankAccount::where('is_active', true)->first();
            }

            if ($bank) {
                $invoice->payment_info = [
                    'account_name' => $bank->account_name,
                    'banks' => [
                        [
                            'bank_name' => $bank->bank_name,
                            'account_number' => $bank->account_number,
                            'currency' => $bank->currency,
                        ],
                    ],
                ];
                $invoice->bank_account_id = $bank->id;
            }
        }
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        if ($invoice->wasChanged('status') && $invoice->status === InvoiceStatus::Submitted) {
            app(SignatureService::class)->notifyNextApprovers($invoice);
        }
    }
}
