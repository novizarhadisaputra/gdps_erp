<?php

namespace Modules\Finance\Observers;

use Modules\CRM\Enums\SalesOrderType;
use Modules\CRM\Models\SalesOrder;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\AccrueRevenueItem;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Services\AccrualReversalService;
use Modules\MasterData\Models\BankAccount;
use Modules\MasterData\Services\SignatureService;
use Modules\Project\Models\WorkCompletionReport;

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
            if ($source instanceof SalesOrder) {
                $isInternal = $source->type->value === SalesOrderType::Internal->value;
            } elseif ($source instanceof WorkCompletionReport && $source->sourceable instanceof SalesOrder) {
                $isInternal = $source->sourceable->type->value === SalesOrderType::Internal->value;
            }

            if ($isInternal) {
                $bank = BankAccount::where('account_name', 'like', '%Internal%')
                    ->where('is_active', true)
                    ->first();
            }

            if (! $bank) {
                $bank = BankAccount::where('is_active', true)->first();
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
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        if ($invoice->work_completion_report_id) {
            AccrueRevenueItem::where('bapp_id', $invoice->work_completion_report_id)
                ->whereNull('invoice_id')
                ->update(['invoice_id' => $invoice->id]);
        }
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        if ($invoice->wasChanged('status') && $invoice->status === InvoiceStatus::Submitted) {
            app(SignatureService::class)->notifyNextApprovers($invoice);

            // Trigger Accrual Reversal logic
            app(AccrualReversalService::class)->reverseAccrualsForInvoice($invoice);
        }
    }
}
