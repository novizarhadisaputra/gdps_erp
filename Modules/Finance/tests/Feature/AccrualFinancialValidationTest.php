<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Finance\Enums\AccrueInvoiceMappingStatus;
use Modules\Finance\Enums\AccrueRevenueStatus;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\AccountMapping;
use Modules\Finance\Models\AccrueInvoiceMapping;
use Modules\Finance\Models\AccrueRevenue;
use Modules\Finance\Models\AccrueRevenueItem;
use Modules\Finance\Models\Invoice;
use Tests\TestCase;

class AccrualFinancialValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Gate::before(fn () => true);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    protected function setupJournalMappings($customer, $projectArea, $revenueType)
    {
        $coaAccrual = \Modules\Finance\Models\ChartOfAccount::factory()->create(['name' => 'Accrual Account']);
        $coaRevenue = \Modules\Finance\Models\ChartOfAccount::factory()->create(['name' => 'Revenue Account']);
        $coaAR = \Modules\Finance\Models\ChartOfAccount::factory()->create(['name' => 'AR Account']);
        $coaTax = \Modules\Finance\Models\ChartOfAccount::factory()->create(['name' => 'Tax Account']);

        // Accrual Mapping
        AccountMapping::create([
            'type' => 'accrual',
            'mappable_id' => $projectArea->id,
            'mappable_type' => \Modules\MasterData\Models\ProjectArea::class,
            'revenue_type_id' => $revenueType->id,
            'chart_of_account_id' => $coaAccrual->id,
        ]);

        // Revenue Mapping
        AccountMapping::create([
            'type' => 'revenue',
            'mappable_id' => $projectArea->id,
            'mappable_type' => \Modules\MasterData\Models\ProjectArea::class,
            'revenue_type_id' => $revenueType->id,
            'chart_of_account_id' => $coaRevenue->id,
        ]);

        // AR Mapping
        AccountMapping::create([
            'type' => 'receivable',
            'mappable_id' => $customer->id,
            'mappable_type' => \Modules\CRM\Models\Customer::class,
            'chart_of_account_id' => $coaAR->id,
        ]);

        // Tax Mapping (VAT Out)
        AccountMapping::create([
            'type' => 'tax',
            'mappable_id' => $projectArea->id,
            'mappable_type' => \Modules\MasterData\Models\ProjectArea::class,
            'chart_of_account_id' => $coaTax->id,
        ]);
    }

    /** @test */
    public function it_creates_journals_and_reverses_correctly()
    {
        $customer = \Modules\CRM\Models\Customer::factory()->create();
        $projectArea = \Modules\MasterData\Models\ProjectArea::factory()->create();
        $revenueType = \Modules\MasterData\Models\RevenueType::factory()->create(['code' => 'main']);

        $this->setupJournalMappings($customer, $projectArea, $revenueType);

        // 1. Setup Accrual (Rp 100.000.000)
        $accrue = AccrueRevenue::factory()->create([
            'status' => AccrueRevenueStatus::Draft,
            'customer_id' => $customer->id,
            'project_area_id' => $projectArea->id,
        ]);
        $item = AccrueRevenueItem::factory()->create([
            'accrue_revenue_id' => $accrue->id,
            'revenue_type_id' => $revenueType->id,
            'amount_estimated' => 100000000,
            'amount_actual' => 0,
        ]);

        // Submit to Finance (Draft -> Open)
        $accrue->update(['status' => AccrueRevenueStatus::Open]);

        // Check Accrual Journal
        $this->assertDatabaseHas('journal_entries', [
            'reference_id' => $accrue->id,
            'reference_type' => AccrueRevenue::class,
        ]);

        // 2. Create Invoice for the same amount
        $invoice = Invoice::factory()->create([
            'status' => InvoiceStatus::Draft,
            'customer_id' => $customer->id,
            'project_area_id' => $projectArea->id,
            'total_amount' => 111000000, // 100M + 11M tax
            'tax_amount' => 11000000,
            'items' => [
                [
                    'item_name' => 'Service Fee',
                    'revenue_type_code' => 'main',
                    'total_price' => 100000000,
                ],
            ],
        ]);

        // Map Invoice to Accrual
        AccrueInvoiceMapping::create([
            'accrue_revenue_item_id' => $item->id,
            'invoice_id' => $invoice->id,
            'allocated_amount' => 100000000,
            'reverse_amount' => 100000000,
            'status' => AccrueInvoiceMappingStatus::Active,
        ]);

        // Approve Invoice
        $invoice->update(['status' => InvoiceStatus::Approved]);

        // 3. Verify Journals
        $this->assertDatabaseHas('journal_entries', [
            'reference_id' => $invoice->id,
            'reference_type' => Invoice::class,
        ]);

        // 4. Verify Accrual Item Status
        $item->refresh();
        $this->assertEquals(100000000, $item->amount_actual);
        $this->assertTrue($item->is_reversed);
    }

    /** @test */
    public function it_restores_accrual_when_invoice_is_cancelled()
    {
        $customer = \Modules\CRM\Models\Customer::factory()->create();
        $projectArea = \Modules\MasterData\Models\ProjectArea::factory()->create();
        $revenueType = \Modules\MasterData\Models\RevenueType::factory()->create(['code' => 'main']);

        $this->setupJournalMappings($customer, $projectArea, $revenueType);

        // 1. Setup Accrual & Invoice
        $accrue = AccrueRevenue::factory()->create([
            'status' => AccrueRevenueStatus::Open,
            'customer_id' => $customer->id,
            'project_area_id' => $projectArea->id,
        ]);
        $item = AccrueRevenueItem::factory()->create([
            'accrue_revenue_id' => $accrue->id,
            'revenue_type_id' => $revenueType->id,
            'amount_estimated' => 100000000,
        ]);

        $invoice = Invoice::factory()->create([
            'status' => InvoiceStatus::Draft, // Start as draft to allow approval -> cancellation
            'customer_id' => $customer->id,
            'project_area_id' => $projectArea->id,
            'total_amount' => 111000000,
            'tax_amount' => 11000000,
            'items' => [
                [
                    'item_name' => 'Service Fee',
                    'revenue_type_code' => 'main',
                    'total_price' => 100000000,
                ],
            ],
        ]);

        AccrueInvoiceMapping::create([
            'accrue_revenue_item_id' => $item->id,
            'invoice_id' => $invoice->id,
            'allocated_amount' => 100000000,
            'reverse_amount' => 100000000,
            'status' => AccrueInvoiceMappingStatus::Active,
        ]);

        // Transition to Approved (Generates Journals)
        $invoice->update(['status' => InvoiceStatus::Approved]);

        // Update item to reflect reversal (usually done by service, but ensuring state here)
        $item->update(['amount_actual' => 100000000, 'is_reversed' => true]);

        // 2. Cancel Invoice
        $invoice->update(['status' => InvoiceStatus::Cancelled]);

        // 3. Verify Restoration
        $item->refresh();
        $this->assertEquals(0, $item->amount_actual);
        $this->assertFalse($item->is_reversed);

        // Check for Cancellation Journals (posted)
        $this->assertDatabaseHas('journal_entries', [
            'reference_id' => $invoice->id,
            'status' => 'posted',
        ]);
    }
}
