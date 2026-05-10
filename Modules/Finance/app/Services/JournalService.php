<?php

namespace Modules\Finance\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Enums\AccrueInvoiceMappingStatus;
use Modules\Finance\Models\AccrueInvoiceMapping;
use Modules\Finance\Models\AccrueRevenue;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Models\JournalItem;
use Modules\MasterData\Models\BankAccount;
use Modules\MasterData\Models\RevenueType;

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
                'total_amount' => $record->total_amount_estimated,
                'status' => 'draft',
                'created_by' => auth()->id() ?? User::where('email', 'like', '%admin%')->first()?->id,
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
                    'debit' => $item->amount_estimated,
                    'credit' => 0,
                    'note' => $item->revenueType->name,
                ]);

                // Credit: Revenue
                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $revenueMapping->chart_of_account_id,
                    'debit' => 0,
                    'credit' => $item->amount_estimated,
                    'note' => $item->revenueType->name,
                ]);
            }

            return $entry;
        });
    }

    /**
     * Generate automated Journal Entry from an Invoice.
     * Logic: Dr Accounts Receivable | Cr Revenue | Cr VAT Out
     */
    public function generateFromInvoice(Invoice $invoice): ?JournalEntry
    {
        // Prevent duplicates
        $existing = JournalEntry::where('reference_id', $invoice->id)
            ->where('reference_type', Invoice::class)
            ->where('description', 'not like', '%Reversal%')
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($invoice) {
            $year = date('Y');
            $shortYear = date('y');
            $latest = JournalEntry::where('year', $year)->orderBy('sequence_number', 'desc')->first();
            $sequence = $latest ? $latest->sequence_number + 1 : 1;

            $entry = JournalEntry::create([
                'number' => sprintf('GDPS/UB/JV-%03d/%s', $sequence, $shortYear),
                'sequence_number' => $sequence,
                'year' => (int) $year,
                'date' => $invoice->invoice_date ?? now(),
                'description' => "Invoice Journal for {$invoice->number} - {$invoice->customer->name}",
                'reference_id' => $invoice->id,
                'reference_type' => Invoice::class,
                'total_amount' => $invoice->total_amount,
                'status' => 'draft',
                'created_by' => auth()->id() ?? User::where('email', 'like', '%admin%')->first()?->id,
            ]);

            $projectArea = $invoice->projectArea;
            $customer = $invoice->customer;
            $revenueSegmentId = $invoice->sourceable?->project?->revenue_segment_id;

            // 1. Debit: Accounts Receivable
            $arMapping = $this->mappingService->resolveAccountMapping('receivable', $projectArea, $customer);
            if (! $arMapping) {
                throw new \Exception("Missing AR account mapping for customer: {$customer->name}");
            }

            JournalItem::create([
                'journal_entry_id' => $entry->id,
                'chart_of_account_id' => $arMapping->chart_of_account_id,
                'debit' => $invoice->total_amount,
                'credit' => 0,
                'note' => "AR: {$invoice->number}",
            ]);

            // 2. Credit: Revenue (Iterate over items)
            $items = is_array($invoice->items) && isset($invoice->items['id']) ? $invoice->items['id'] : $invoice->items;
            foreach ($items as $item) {
                $revenueTypeCode = $item['revenue_type_code'] ?? 'main';
                $revenueType = RevenueType::where('code', $revenueTypeCode)->first();

                $revenueMapping = $this->mappingService->resolveAccountMapping(
                    'revenue',
                    $projectArea,
                    $customer,
                    $revenueType?->id,
                    $revenueSegmentId
                );

                if (! $revenueMapping) {
                    throw new \Exception("Missing revenue mapping for type: {$revenueTypeCode}");
                }

                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $revenueMapping->chart_of_account_id,
                    'debit' => 0,
                    'credit' => (float) $item['total_price'],
                    'note' => "Revenue: {$item['item_name']}",
                ]);
            }

            // 3. Credit: VAT Out (Tax)
            if ($invoice->tax_amount > 0) {
                $taxMapping = $this->mappingService->resolveAccountMapping('tax', $projectArea, $customer);
                if (! $taxMapping) {
                    throw new \Exception('Missing Tax account mapping (VAT Out)');
                }

                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $taxMapping->chart_of_account_id,
                    'debit' => 0,
                    'credit' => $invoice->tax_amount,
                    'note' => "VAT Out: {$invoice->number}",
                ]);
            }

            // 4. Debit: Withholding Taxes (Prepaid Tax)
            $taxDetails = is_array($invoice->tax_details) ? $invoice->tax_details : [];
            foreach ($taxDetails as $detail) {
                $taxId = $detail['tax_id'] ?? null;
                $taxAmount = (float) ($detail['tax_amount'] ?? 0);

                if ($taxAmount <= 0 || ! $taxId) {
                    continue;
                }

                $taxRecord = \Modules\MasterData\Models\Tax::find($taxId);
                $withholdingMapping = $this->mappingService->resolveAccountMapping('withholding_tax', $projectArea, $customer, null, null, $taxId);

                if (! $withholdingMapping) {
                    throw new \Exception('Missing account mapping for tax: '.($taxRecord?->name ?? $taxId));
                }

                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $withholdingMapping->chart_of_account_id,
                    'debit' => $taxAmount,
                    'credit' => 0,
                    'note' => 'Potongan '.($taxRecord?->name ?? 'PPh').': '.$invoice->number,
                ]);
            }

            return $entry;
        });
    }

    /**
     * Generate Reversal Journal Entry for an Invoice that was previously accrued.
     * Logic: Dr Revenue | Cr Accrued Revenue
     */
    public function generateReversalFromInvoice(Invoice $invoice): ?JournalEntry
    {
        $mappings = AccrueInvoiceMapping::where('invoice_id', $invoice->id)
            ->where('status', AccrueInvoiceMappingStatus::Active)
            ->with(['accrueRevenueItem.accrueRevenue', 'accrueRevenueItem.revenueType'])
            ->get();

        if ($mappings->isEmpty()) {
            return null;
        }

        return DB::transaction(function () use ($invoice, $mappings) {
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
                'total_amount' => $mappings->sum('reverse_amount'),
                'status' => 'draft',
                'created_by' => auth()->id() ?? User::where('email', 'like', '%admin%')->first()?->id,
            ]);

            foreach ($mappings as $mapping) {
                $item = $mapping->accrueRevenueItem;
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
                    continue;
                }

                // Reversal: Debit Revenue (to cancel), Credit Accrued Revenue (to clear)
                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $revenueMapping->chart_of_account_id,
                    'debit' => $mapping->reverse_amount,
                    'credit' => 0,
                    'note' => "Reversal: {$item->revenueType->name}",
                ]);

                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $accrualMapping->chart_of_account_id,
                    'debit' => 0,
                    'credit' => $mapping->reverse_amount,
                    'note' => "Reversal: {$item->revenueType->name}",
                ]);

                // Link mapping to reversal journal
                $mapping->update([
                    'reverse_journal_entry_id' => $entry->id,
                    'status' => AccrueInvoiceMappingStatus::Reversed,
                ]);
            }

            return $entry;
        });
    }

    /**
     * Generate automated Journal Entry from an Invoice Payment (Cash Receipt).
     * Logic: Dr Bank/Cash Account | Cr Accounts Receivable
     */
    public function generateFromCashReceipt(Invoice $invoice): ?JournalEntry
    {
        // Prevent duplicates
        $existing = JournalEntry::where('reference_id', $invoice->id)
            ->where('reference_type', Invoice::class)
            ->where('description', 'like', 'Penerimaan Kas%')
            ->first();

        if ($existing) {
            return $existing;
        }

        if (! $invoice->bank_account_id) {
            return null;
        }

        return DB::transaction(function () use ($invoice) {
            $year = date('Y');
            $shortYear = date('y');
            $latest = JournalEntry::where('year', $year)->orderBy('sequence_number', 'desc')->first();
            $sequence = $latest ? $latest->sequence_number + 1 : 1;

            $entry = JournalEntry::create([
                'number' => sprintf('GDPS/UB/JV-%03d/%s', $sequence, $shortYear),
                'sequence_number' => $sequence,
                'year' => (int) $year,
                'date' => now(), // Payment date
                'description' => "Penerimaan Kas untuk {$invoice->number} - {$invoice->customer->name}",
                'reference_id' => $invoice->id,
                'reference_type' => Invoice::class,
                'total_amount' => $invoice->total_amount,
                'status' => 'draft',
                'created_by' => auth()->id() ?? User::where('email', 'like', '%admin%')->first()?->id,
            ]);

            // 1. Debit: Bank/Cash Account (Linked to BankAccount)
            $bankAccount = $invoice->bankAccount;
            $bankCOA = ChartOfAccount::where('accountable_id', $invoice->bank_account_id)
                ->where('accountable_type', BankAccount::class)
                ->first();

            if (! $bankCOA) {
                throw new \Exception("Missing GL account mapping for bank account: {$bankAccount->account_name}");
            }

            JournalItem::create([
                'journal_entry_id' => $entry->id,
                'chart_of_account_id' => $bankCOA->id,
                'debit' => $invoice->total_amount,
                'credit' => 0,
                'note' => "Payment: {$invoice->number} via {$bankAccount->bank_name}",
            ]);

            // 2. Credit: Accounts Receivable
            $arMapping = $this->mappingService->resolveAccountMapping('receivable', $invoice->projectArea, $invoice->customer);
            if (! $arMapping) {
                throw new \Exception("Missing AR account mapping for customer: {$invoice->customer->name}");
            }

            JournalItem::create([
                'journal_entry_id' => $entry->id,
                'chart_of_account_id' => $arMapping->chart_of_account_id,
                'debit' => 0,
                'credit' => $invoice->total_amount,
                'note' => "Clearing AR: {$invoice->number}",
            ]);

            return $entry;
        });
    }
}
