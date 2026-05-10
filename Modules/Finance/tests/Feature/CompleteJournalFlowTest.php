<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Modules\CRM\Models\Customer;
use Modules\Finance\Enums\AccrueInvoiceMappingStatus;
use Modules\Finance\Enums\AccrueRevenueStatus;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\AccountMapping;
use Modules\Finance\Models\AccrueInvoiceMapping;
use Modules\Finance\Models\AccrueRevenue;
use Modules\Finance\Models\AccrueRevenueItem;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\JournalEntry;
use Modules\MasterData\Models\BankAccount;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\RevenueType;
use Modules\Project\Models\Project;
use Tests\TestCase;

class CompleteJournalFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Master Data
        $this->customer = Customer::create([
            'id' => Str::uuid(),
            'name' => 'Complete Flow Customer',
            'email' => 'flow@example.com',
        ]);
        $this->area = ProjectArea::create([
            'id' => Str::uuid(),
            'name' => 'Flow Area',
            'code' => 'FA',
        ]);
        $this->project = Project::create([
            'id' => Str::uuid(),
            'name' => 'Flow Project',
            'customer_id' => $this->customer->id,
            'project_area_id' => $this->area->id,
            'status' => 'active',
        ]);
        $this->revenueType = RevenueType::create([
            'id' => Str::uuid(),
            'code' => 'main',
            'name' => 'Main Revenue',
            'is_active' => true,
        ]);

        // 2. Setup Chart of Accounts
        $this->arAccount = ChartOfAccount::create(['id' => Str::uuid(), 'code' => '110101', 'name' => 'AR', 'account_type' => 'Asset', 'is_active' => true]);
        $this->revAccount = ChartOfAccount::create(['id' => Str::uuid(), 'code' => '410101', 'name' => 'Revenue', 'account_type' => 'Revenue', 'is_active' => true]);
        $this->accrualAccount = ChartOfAccount::create(['id' => Str::uuid(), 'code' => '110102', 'name' => 'Accrual', 'account_type' => 'Asset', 'is_active' => true]);
        $this->taxAccount = ChartOfAccount::create(['id' => Str::uuid(), 'code' => '210101', 'name' => 'Tax', 'account_type' => 'Liability', 'is_active' => true]);
        $this->bankAccountGL = ChartOfAccount::create(['id' => Str::uuid(), 'code' => '110103', 'name' => 'Bank Mandiri GL', 'account_type' => 'Asset', 'is_active' => true]);

        // 3. Setup Bank Account
        $this->bankAccount = BankAccount::create([
            'id' => Str::uuid(),
            'bank_name' => 'Mandiri',
            'account_number' => '123456789',
            'account_name' => 'PT GDPS',
            'is_active' => true,
        ]);

        // Link Bank Account to COA (Polymorphic)
        $this->bankAccountGL->update([
            'accountable_id' => $this->bankAccount->id,
            'accountable_type' => BankAccount::class,
        ]);

        // 4. Setup Account Mappings
        AccountMapping::create(['mappable_type' => Customer::class, 'mappable_id' => $this->customer->id, 'type' => 'receivable', 'chart_of_account_id' => $this->arAccount->id]);
        AccountMapping::create(['mappable_type' => Customer::class, 'mappable_id' => $this->customer->id, 'type' => 'accrual', 'chart_of_account_id' => $this->accrualAccount->id, 'revenue_type_id' => $this->revenueType->id]);
        AccountMapping::create(['mappable_type' => Customer::class, 'mappable_id' => $this->customer->id, 'type' => 'revenue', 'chart_of_account_id' => $this->revAccount->id, 'revenue_type_id' => $this->revenueType->id]);
        AccountMapping::create(['mappable_type' => Customer::class, 'mappable_id' => $this->customer->id, 'type' => 'tax', 'chart_of_account_id' => $this->taxAccount->id]);
    }

    public function test_full_accounting_lifecycle_journals(): void
    {
        // === STEP 1: ACCRUAL JOURNAL ===
        $accrue = AccrueRevenue::create([
            'project_id' => $this->project->id,
            'customer_id' => $this->customer->id,
            'project_area_id' => $this->area->id,
            'accrue_date' => now(),
            'month' => now()->month,
            'year' => now()->year,
            'status' => AccrueRevenueStatus::Draft,
            'company_code' => 'GDPS',
            'work_period' => now(),
            'accrual_period' => now()->startOfMonth(),
        ]);

        $accrueItem = AccrueRevenueItem::create([
            'accrue_revenue_id' => $accrue->id,
            'revenue_type_id' => $this->revenueType->id,
            'revenue_type' => 'Manpower',
            'amount_estimated' => 1000000,
            'amount_actual' => 0,
            'description' => 'Test Accrual',
            'revenue_chart_of_account_id' => $this->revAccount->id,
        ]);

        // Transition to Open to trigger Accrual Journal
        $accrue->update(['status' => AccrueRevenueStatus::Open]);

        $this->assertDatabaseHas('journal_entries', [
            'reference_id' => $accrue->id,
            'reference_type' => AccrueRevenue::class,
            'total_amount' => 1000000,
        ]);

        // === STEP 2 & 3: INVOICE & REVERSAL JOURNALS ===
        $invoice = Invoice::create([
            'customer_id' => $this->customer->id,
            'project_area_id' => $this->area->id,
            'bank_account_id' => $this->bankAccount->id,
            'amount' => 1000000,
            'tax_base_amount' => 1000000,
            'tax_amount' => 110000,
            'total_amount' => 1110000,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => InvoiceStatus::Draft,
            'number' => 'INV/FLOW/001',
            'items' => [
                [
                    'revenue_type_code' => 'main',
                    'total_price' => 1000000,
                    'item_name' => 'Manpower Services',
                ],
            ],
        ]);

        AccrueInvoiceMapping::create([
            'accrue_revenue_item_id' => $accrueItem->id,
            'invoice_id' => $invoice->id,
            'allocated_amount' => 1000000,
            'reverse_amount' => 1000000,
            'status' => AccrueInvoiceMappingStatus::Active,
        ]);

        // Approve Invoice to trigger Invoice + Reversal Journals
        $invoice->update(['status' => InvoiceStatus::Approved]);

        // Verify Invoice Journal
        $this->assertDatabaseHas('journal_entries', [
            'reference_id' => $invoice->id,
            'reference_type' => Invoice::class,
            'total_amount' => 1110000,
        ]);

        // Verify Reversal Journal
        $reversal = JournalEntry::where('reference_id', $invoice->id)
            ->where('description', 'like', '%Reversal%')
            ->first();
        $this->assertNotNull($reversal);
        $this->assertEquals(1000000, $reversal->total_amount);

        // === STEP 4: CASH RECEIPT JOURNAL ===
        // Pay Invoice to trigger Cash Receipt Journal
        $invoice->update(['status' => InvoiceStatus::Paid]);

        $cashReceiptJournal = JournalEntry::where('reference_id', $invoice->id)
            ->where('description', 'like', '%Penerimaan Kas%')
            ->first();

        $this->assertNotNull($cashReceiptJournal);
        $this->assertEquals(1110000, $cashReceiptJournal->total_amount);

        // Verify specific lines in Cash Receipt Journal
        $this->assertDatabaseHas('journal_items', [
            'journal_entry_id' => $cashReceiptJournal->id,
            'chart_of_account_id' => $this->bankAccountGL->id,
            'debit' => 1110000,
            'credit' => 0,
        ]);

        $this->assertDatabaseHas('journal_items', [
            'journal_entry_id' => $cashReceiptJournal->id,
            'chart_of_account_id' => $this->arAccount->id,
            'debit' => 0,
            'credit' => 1110000,
        ]);
    }

    public function test_partial_reversal_logic(): void
    {
        // 1. Setup Accrual (2,000,000)
        $accrue = AccrueRevenue::create([
            'project_id' => $this->project->id,
            'customer_id' => $this->customer->id,
            'project_area_id' => $this->area->id,
            'accrue_date' => now(),
            'month' => now()->month,
            'year' => now()->year,
            'status' => AccrueRevenueStatus::Draft,
            'company_code' => 'GDPS',
            'work_period' => now(),
            'accrual_period' => now()->startOfMonth(),
        ]);

        $accrueItem = AccrueRevenueItem::create([
            'accrue_revenue_id' => $accrue->id,
            'revenue_type_id' => $this->revenueType->id,
            'revenue_type' => 'Manpower',
            'amount_estimated' => 2000000,
            'amount_actual' => 0,
            'description' => 'Test Partial Reversal',
            'revenue_chart_of_account_id' => $this->revAccount->id,
        ]);

        // 2. Invoice 1 (1,200,000)
        $invoice1 = Invoice::create([
            'customer_id' => $this->customer->id,
            'project_area_id' => $this->area->id,
            'amount' => 1200000,
            'total_amount' => 1332000,
            'invoice_date' => now(),
            'status' => InvoiceStatus::Draft,
            'number' => 'INV/PART/001',
            'items' => [['revenue_type_code' => 'main', 'total_price' => 1200000, 'item_name' => 'Partial Item 1']],
        ]);

        AccrueInvoiceMapping::create([
            'accrue_revenue_item_id' => $accrueItem->id,
            'invoice_id' => $invoice1->id,
            'allocated_amount' => 1200000,
            'reverse_amount' => 1200000,
            'status' => AccrueInvoiceMappingStatus::Active,
        ]);

        $invoice1->update(['status' => InvoiceStatus::Approved]);

        $accrueItem->refresh();
        $this->assertEquals(1200000, $accrueItem->amount_actual);
        $this->assertFalse($accrueItem->is_reversed); // Still 800k remaining

        // 3. Invoice 2 (800,000)
        $invoice2 = Invoice::create([
            'customer_id' => $this->customer->id,
            'project_area_id' => $this->area->id,
            'amount' => 800000,
            'total_amount' => 888000,
            'invoice_date' => now(),
            'status' => InvoiceStatus::Draft,
            'number' => 'INV/PART/002',
            'items' => [['revenue_type_code' => 'main', 'total_price' => 800000, 'item_name' => 'Partial Item 2']],
        ]);

        AccrueInvoiceMapping::create([
            'accrue_revenue_item_id' => $accrueItem->id,
            'invoice_id' => $invoice2->id,
            'allocated_amount' => 800000,
            'reverse_amount' => 800000,
            'status' => AccrueInvoiceMappingStatus::Active,
        ]);

        $invoice2->update(['status' => InvoiceStatus::Approved]);

        $accrueItem->refresh();
        $this->assertEquals(2000000, $accrueItem->amount_actual);
        $this->assertTrue($accrueItem->is_reversed); // Fully reversed
    }

    public function test_it_prevents_duplicate_journals_on_repeated_approval(): void
    {
        $invoice = Invoice::create([
            'customer_id' => $this->customer->id,
            'project_area_id' => $this->area->id,
            'amount' => 1000000,
            'total_amount' => 1110000,
            'invoice_date' => now(),
            'status' => InvoiceStatus::Draft,
            'number' => 'INV/DUP/001',
            'items' => [['revenue_type_code' => 'main', 'total_price' => 1000000, 'item_name' => 'Duplicate Test Item']],
        ]);

        // First approval
        $invoice->update(['status' => InvoiceStatus::Approved]);
        $initialCount = JournalEntry::where('reference_id', $invoice->id)->count();

        // Repeated "update" to Approved (should not happen in UI, but testing robust service)
        $invoice->update(['status' => InvoiceStatus::Approved]);
        $newCount = JournalEntry::where('reference_id', $invoice->id)->count();

        $this->assertEquals($initialCount, $newCount, 'Duplicate journals were created');
    }
}
