<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\CRM\Models\Customer;
use Modules\Finance\Enums\AccrueRevenueStatus;
use Modules\Finance\Models\AccountMapping;
use Modules\Finance\Models\AccrueRevenue;
use Modules\Finance\Models\AccrueRevenueItem;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\JournalEntry;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\RevenueType;
use Modules\Project\Models\Project;
use Tests\TestCase;

class AccrualExpenseWorkflowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

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
        $this->expenseType = RevenueType::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'code' => 'other_direct',
            'name' => 'Direct Cost',
            'is_active' => true,
        ]);

        $this->expAccount = ChartOfAccount::create(['id' => \Illuminate\Support\Str::uuid(), 'code' => '510101', 'name' => 'Expense', 'account_type' => 'Expense', 'is_active' => true]);
        $this->expAccrualAccount = ChartOfAccount::create(['id' => \Illuminate\Support\Str::uuid(), 'code' => '210102', 'name' => 'Accrued Expense', 'account_type' => 'Liability', 'is_active' => true]);

        // Setup Expense Mappings
        AccountMapping::create([
            'mappable_type' => Customer::class,
            'mappable_id' => $this->customer->id,
            'type' => 'expense',
            'chart_of_account_id' => $this->expAccount->id,
            'revenue_type_id' => $this->expenseType->id,
        ]);

        AccountMapping::create([
            'mappable_type' => Customer::class,
            'mappable_id' => $this->customer->id,
            'type' => 'expense_accrual',
            'chart_of_account_id' => $this->expAccrualAccount->id,
            'revenue_type_id' => $this->expenseType->id,
        ]);
    }

    public function test_it_generates_expense_accrual_journals_correctly(): void
    {
        // 1. Create Accrue with Expense Item
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
            'type' => 'expense',
            'revenue_type_id' => $this->expenseType->id,
            'amount_expense_estimated' => 500000,
            'description' => 'Test Expense Accrual',
        ]);

        // 2. Finalize Accrue (triggers observer -> JournalService)
        $accrue->update(['status' => AccrueRevenueStatus::Open]);

        // 3. Assert Journal
        $journal = JournalEntry::where('reference_id', $accrue->id)
            ->where('reference_type', AccrueRevenue::class)
            ->first();

        $this->assertNotNull($journal);
        $this->assertEquals(500000, $journal->total_amount);

        // Check Debit: Expense
        $this->assertDatabaseHas('journal_items', [
            'journal_entry_id' => $journal->id,
            'chart_of_account_id' => $this->expAccount->id,
            'debit' => 500000,
            'credit' => 0,
        ]);

        // Check Credit: Accrued Expense
        $this->assertDatabaseHas('journal_items', [
            'journal_entry_id' => $journal->id,
            'chart_of_account_id' => $this->expAccrualAccount->id,
            'debit' => 0,
            'credit' => 500000,
        ]);
    }
}
