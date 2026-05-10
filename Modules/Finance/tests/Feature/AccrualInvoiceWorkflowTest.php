<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\CRM\Models\Customer;
use Modules\Finance\Enums\AccrueRevenueStatus;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\AccountMapping;
use Modules\Finance\Models\AccrueInvoiceMapping;
use Modules\Finance\Models\AccrueRevenue;
use Modules\Finance\Models\AccrueRevenueItem;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\JournalEntry;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\RevenueType;
use Modules\Project\Models\Project;
use Tests\TestCase;

class AccrualInvoiceWorkflowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup essential master data and mappings
        $this->customer = Customer::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);
        $this->area = ProjectArea::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => 'Test Area',
            'code' => 'TA',
        ]);
        $this->project = Project::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => 'Test Project',
            'customer_id' => $this->customer->id,
            'project_area_id' => $this->area->id,
            'status' => 'active',
        ]);
        $this->revenueType = RevenueType::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'code' => 'main',
            'name' => 'Main Revenue',
            'is_active' => true,
        ]);

        // Setup Accounts
        $this->arAccount = ChartOfAccount::create(['id' => \Illuminate\Support\Str::uuid(), 'code' => '110101', 'name' => 'AR', 'account_type' => 'Asset', 'is_active' => true]);
        $this->revAccount = ChartOfAccount::create(['id' => \Illuminate\Support\Str::uuid(), 'code' => '410101', 'name' => 'Revenue', 'account_type' => 'Revenue', 'is_active' => true]);
        $this->accrualAccount = ChartOfAccount::create(['id' => \Illuminate\Support\Str::uuid(), 'code' => '110102', 'name' => 'Accrual', 'account_type' => 'Asset', 'is_active' => true]);
        $this->taxAccount = ChartOfAccount::create(['id' => \Illuminate\Support\Str::uuid(), 'code' => '210101', 'name' => 'Tax', 'account_type' => 'Liability', 'is_active' => true]);

        // Setup Mappings
        AccountMapping::create([
            'mappable_type' => Customer::class,
            'mappable_id' => $this->customer->id,
            'type' => 'receivable',
            'chart_of_account_id' => $this->arAccount->id,
        ]);

        AccountMapping::create([
            'mappable_type' => Customer::class,
            'mappable_id' => $this->customer->id,
            'type' => 'accrual',
            'chart_of_account_id' => $this->accrualAccount->id,
            'revenue_type_id' => $this->revenueType->id,
        ]);

        AccountMapping::create([
            'mappable_type' => Customer::class,
            'mappable_id' => $this->customer->id,
            'type' => 'revenue',
            'chart_of_account_id' => $this->revAccount->id,
            'revenue_type_id' => $this->revenueType->id,
        ]);

        AccountMapping::create([
            'mappable_type' => Customer::class,
            'mappable_id' => $this->customer->id,
            'type' => 'tax',
            'chart_of_account_id' => $this->taxAccount->id,
        ]);
    }

    public function test_it_generates_invoice_and_reversal_journals_on_approval(): void
    {
        // 1. Create Accrue Revenue
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

        // 2. Create Invoice
        $invoice = Invoice::create([
            'customer_id' => $this->customer->id,
            'project_area_id' => $this->area->id,
            'amount' => 800000,
            'tax_base_amount' => 800000,
            'tax_amount' => 80000,
            'total_amount' => 880000,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => InvoiceStatus::Draft,
            'number' => 'INV/TEST/001',
            'items' => [
                [
                    'revenue_type_code' => 'main',
                    'total_price' => 800000,
                    'item_name' => 'Manpower Services',
                ],
            ],
        ]);

        // 3. Link them
        AccrueInvoiceMapping::create([
            'accrue_revenue_item_id' => $accrueItem->id,
            'invoice_id' => $invoice->id,
            'allocated_amount' => 800000,
            'reverse_amount' => 800000,
            'status' => \Modules\Finance\Enums\AccrueInvoiceMappingStatus::Active,
        ]);

        // 4. Approve Invoice
        $invoice->update(['status' => InvoiceStatus::Approved]);

        // 5. Assertions - Invoice Journal
        $this->assertDatabaseHas('journal_entries', [
            'reference_id' => $invoice->id,
            'reference_type' => Invoice::class,
            'total_amount' => 880000,
        ]);

        // 6. Assertions - Reversal Journal
        $reversalJournal = JournalEntry::where('reference_id', $invoice->id)
            ->where('description', 'like', '%Reversal%')
            ->first();

        $this->assertNotNull($reversalJournal);
        $this->assertEquals(800000, $reversalJournal->total_amount);

        // 7. Assertions - Mapping Status
        $mapping = AccrueInvoiceMapping::where('invoice_id', $invoice->id)->first();
        $this->assertEquals(\Modules\Finance\Enums\AccrueInvoiceMappingStatus::Reversed, $mapping->status);
        $this->assertEquals($reversalJournal->id, $mapping->reverse_journal_entry_id);

        // 8. Assertions - Accrue Item Actual Amount
        $accrueItem->refresh();
        $this->assertEquals(800000, $accrueItem->amount_actual);
        $this->assertFalse($accrueItem->is_reversed); // Still has 200k balance
    }
}
