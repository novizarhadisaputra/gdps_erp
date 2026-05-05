<?php

namespace Modules\Finance\Services;

use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\AccrueRevenue;
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
            // 1. Create the Header
            $entry = JournalEntry::create([
                'number' => $this->generateVoucherNumber('JV-ACC'),
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
     * Generate a unique Journal Voucher number.
     * Format: {Prefix}/{YYYY}/{MM}/{Sequence}
     */
    protected function generateVoucherNumber(string $prefix): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $count = JournalEntry::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;

        return sprintf('%s/%s/%s/%05d', $prefix, $year, $month, $count);
    }
}
