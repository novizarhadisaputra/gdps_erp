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
        if (filled($invoice->number)) {
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

        if (empty($invoice->project_area_id)) {
            $source = $invoice->sourceable;
            if ($source instanceof WorkCompletionReport) {
                $invoice->project_area_id = $source->project_area_id;
            } elseif ($source instanceof SalesOrder) {
                $invoice->project_area_id = $source->project?->project_area_id ?? $source->proposal?->project_area_id;
            }
        }

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
     * Handle the Invoice "saving" event.
     */
    public function saving(Invoice $invoice): void
    {
        if (empty($invoice->snapshot) && $invoice->sourceable && isset($invoice->sourceable->snapshot)) {
            $invoice->snapshot = $invoice->sourceable->snapshot;
        }
    }

    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        if ($invoice->work_completion_report_id) {
            AccrueRevenueItem::where('work_completion_report_id', $invoice->work_completion_report_id)
                ->whereNull('invoice_id')
                ->update(['invoice_id' => $invoice->id]);
        }
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        if ($invoice->wasChanged('status')) {
            if ($invoice->status === InvoiceStatus::Submitted) {
                app(SignatureService::class)->notifyNextApprovers($invoice);

                // Trigger Accrual Reversal logic
                app(AccrualReversalService::class)->reverseAccrualsForInvoice($invoice);
            }

            // Revision Logic: Capture snapshot if status changed back to Draft from a non-Draft status
            $originalStatus = $invoice->getOriginal('status');
            if ($invoice->status === InvoiceStatus::Draft && $originalStatus !== InvoiceStatus::Draft) {
                $revision = $invoice->revisions()->create([
                    'number' => $originalStatus !== null ? $invoice->getOriginal('number') : $invoice->number,
                    'sequence_number' => $invoice->getOriginal('revision_number') ?? 0,
                    'year' => date('Y'),
                    'snapshot' => $invoice->getRawOriginal(),
                    'reason' => request()->input('reason') ?? 'Manual revision triggered.',
                    'user_id' => auth()->id(),
                ]);

                // Copy Media Snapshots
                foreach (['payment_proof', 'signed_invoice'] as $collection) {
                    $invoice->getMedia($collection)->each(function ($media) use ($revision, $collection) {
                        $media->copy($revision, $collection);
                    });
                }

                // Update main document to reflect revision status
                $newRevisionNumber = $invoice->revision_number + 1;
                $shortYear = date('y', strtotime($invoice->invoice_date ?? $invoice->created_at));

                $baseNumber = sprintf('GDPS/UB/INV-%03d', $invoice->sequence_number);
                $newNumber = sprintf('%s/REV/%02d/%s', $baseNumber, $newRevisionNumber, $shortYear);

                $invoice->updateQuietly([
                    'revision_number' => $newRevisionNumber,
                    'previous_code' => $invoice->number,
                    'number' => $newNumber,
                ]);
            }
        }
    }

    /**
     * Handle the Invoice "saved" event.
     */
    public function saved(Invoice $invoice): void
    {
        // Automation: If Signed Invoice is uploaded and it's fully approved internally
        if ($invoice->status === InvoiceStatus::Submitted && $invoice->isFullyApproved() && $invoice->hasMedia('signed_invoice')) {
            $invoice->updateQuietly(['status' => InvoiceStatus::Approved]);
        }

        // Automation: If Payment Proof is uploaded
        if (in_array($invoice->status, [InvoiceStatus::Sent, InvoiceStatus::Partial, InvoiceStatus::Overdue]) && $invoice->hasMedia('payment_proof')) {
            $invoice->updateQuietly(['status' => InvoiceStatus::Paid]);
        }
    }
}
