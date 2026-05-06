<?php

namespace Modules\Finance\Services;

use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\AccrueRevenue;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Models\JournalItem;

class JournalService
{
    public function __construct(
        protected AccrualMappingService $mappingService
    ) {}

    /**
     * Generate automated Journal Entry from an Accrue Revenue document.
     */
    public function generateFromAccrueRevenue(AccrueRevenue $record): ?JournalEntry
    {
        // Prevent duplicates
        $existing = JournalEntry::where('reference_id', $record->id)
            ->where('reference_type', AccrueRevenue::class)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($record) {
            $year = date('Y');
            $shortYear = date('y');
            $latest = JournalEntry::where('year', $year)->orderBy('sequence_number', 'desc')->first();
            $sequence = $latest ? $latest->sequence_number + 1 : 1;

            // 1. Create the Header
            $entry = JournalEntry::create([
                'number' => sprintf('GDPS/UB/JV-%03d/%s', $sequence, $shortYear),
                'sequence_number' => $sequence,
                'year' => (int) $year,
                'date' => $record->accrue_date ?? now(),
                'description' => "Automated Accrual for {$record->number} - {$record->customer->name}",
                'reference_id' => $record->id,
                'reference_type' => AccrueRevenue::class,
                'total_amount' => $record->total_amount,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            $projectArea = $record->projectArea;
            $customer = $record->customer;
            $revenueSegmentId = $record->project?->revenue_segment_id;

            // 2. Process Items
            foreach ($record->items as $item) {
                // Resolved accounts
                $accrualMapping = $this->mappingService->resolveAccountMapping(
                    'accrual',
                    $projectArea,
                    $customer,
                    $item->revenue_type_id,
                    $revenueSegmentId
                );

                $revenueMapping = $this->mappingService->resolveAccountMapping(
                    'revenue',
                    $projectArea,
                    $customer,
                    $item->revenue_type_id,
                    $revenueSegmentId
                );

                if (! $accrualMapping || ! $revenueMapping) {
                    throw new \Exception("Missing account mapping for revenue type: {$item->revenueType->name}");
                }

                // Debit: Accrued Revenue
                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $accrualMapping->chart_of_account_id,
                    'debit' => $item->amount,
                    'credit' => 0,
                    'note' => $item->revenueType->name,
                ]);

                // Credit: Revenue
                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $revenueMapping->chart_of_account_id,
                    'debit' => 0,
                    'credit' => $item->amount,
                    'note' => $item->revenueType->name,
                ]);
            }

            return $entry;
        });
    }

    /**
     * Generate Reversal Journal Entry for an Invoice that was previously accrued.
     */
    public function generateReversalFromInvoice(Invoice $invoice): ?JournalEntry
    {
        $items = $invoice->accrueRevenueItems()
            ->where('is_reversed', false)
            ->with(['accrueRevenue', 'revenueType'])
            ->get();

        if ($items->isEmpty()) {
            return null;
        }

        return DB::transaction(function () use ($invoice, $items) {
            $year = date('Y');
            $shortYear = date('y');
            $latest = JournalEntry::where('year', $year)->orderBy('sequence_number', 'desc')->first();
            $sequence = $latest ? $latest->sequence_number + 1 : 1;

            $entry = JournalEntry::create([
                'number' => sprintf('GDPS/UB/JV-%03d/%s', $sequence, $shortYear),
                'sequence_number' => $sequence,
                'year' => (int) $year,
                'date' => $invoice->invoice_date ?? now(),
                'description' => "Automated Reversal for Invoice {$invoice->number} - {$invoice->customer->name}",
                'reference_id' => $invoice->id,
                'reference_type' => Invoice::class,
                'total_amount' => $items->sum('amount_estimated'),
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($items as $item) {
                $accrueRevenue = $item->accrueRevenue;
                $projectArea = $accrueRevenue->projectArea;
                $customer = $accrueRevenue->customer;
                $revenueSegmentId = $accrueRevenue->project?->revenue_segment_id;

                $accrualMapping = $this->mappingService->resolveAccountMapping(
                    'accrual',
                    $projectArea,
                    $customer,
                    $item->revenue_type_id,
                    $revenueSegmentId
                );

                $revenueMapping = $this->mappingService->resolveAccountMapping(
                    'revenue',
                    $projectArea,
                    $customer,
                    $item->revenue_type_id,
                    $revenueSegmentId
                );

                if (! $accrualMapping || ! $revenueMapping) {
                    continue; // Skip if mapping missing, but ideally logged
                }

                // Reversal: Debit Revenue (to cancel), Credit Accrued Revenue (to clear)
                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $revenueMapping->chart_of_account_id,
                    'debit' => $item->amount_estimated,
                    'credit' => 0,
                    'note' => "Reversal: {$item->revenueType->name}",
                ]);

                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $accrualMapping->chart_of_account_id,
                    'debit' => 0,
                    'credit' => $item->amount_estimated,
                    'note' => "Reversal: {$item->revenueType->name}",
                ]);
            }

            return $entry;
        });
    }
}
