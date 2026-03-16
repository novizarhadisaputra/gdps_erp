<?php

namespace Modules\Project\Observers;

use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\Invoice;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Models\WorkCompletionReport;

class WorkCompletionReportObserver
{
    /**
     * Handle the WorkCompletionReport "updated" event.
     */
    public function updated(WorkCompletionReport $report): void
    {
        if ($report->wasChanged('status') && $report->status === WorkCompletionStatus::Signed) {
            // Auto-create Invoice (Draft)
            Invoice::create([
                'sales_order_id' => $report->sales_order_id,
                'work_completion_report_id' => $report->id,
                'customer_id' => $report->customer_id,
                'invoice_number' => 'INV-'.str_replace('BAPP-', '', $report->report_number),
                'invoice_date' => now(),
                'due_date' => now()->addDays(30), // Default 30 days
                'amount' => $report->salesOrder?->amount ?? 0, // Should ideally be proportional to progress
                'status' => InvoiceStatus::Draft,
            ]);
        }
    }
}
