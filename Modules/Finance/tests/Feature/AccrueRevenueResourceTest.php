<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Modules\Finance\Enums\AccrueRevenueStatus;
use Modules\Finance\Models\AccrueRevenue;
use Modules\Finance\Models\AccrueRevenueItem;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;
use Modules\Project\Models\Project;
use Tests\TestCase;

class AccrueRevenueResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Gate::before(fn () => true);

        $roleId = (string) Str::uuid();
        DB::table('roles')->insert([
            'id' => $roleId,
            'name' => 'super_admin',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);
    }

    public function test_can_list_accrue_revenues(): void
    {
        AccrueRevenue::factory()->count(3)->create();

        $this->get(\Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\AccrueRevenueResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_accrue_revenue_auto_numbering(): void
    {
        $accrue = AccrueRevenue::factory()->create();
        $this->assertMatchesRegularExpression('/GDPS\/UB\/ACR-\d{3}\/\d{2}/', $accrue->number);
    }

    public function test_accrue_revenue_totals_calculation(): void
    {
        // 1. Create Accrue Revenue
        $accrue = AccrueRevenue::factory()->create(['status' => AccrueRevenueStatus::Draft]);

        // 2. Create Items
        AccrueRevenueItem::factory()->create([
            'accrue_revenue_id' => $accrue->id,
            'amount_estimated' => 1000,
            'amount_actual' => 800,
            'amount_expense_estimated' => 500,
            'amount_expense_actual' => 400,
        ]);

        AccrueRevenueItem::factory()->create([
            'accrue_revenue_id' => $accrue->id,
            'amount_estimated' => 2000,
            'amount_actual' => 1500,
            'amount_expense_estimated' => 1000,
            'amount_expense_actual' => 900,
        ]);

        // 3. Trigger saved observer
        $accrue->save();
        $accrue->refresh();

        // 4. Assert totals
        $this->assertEquals(3000, $accrue->total_amount_estimated);
        $this->assertEquals(2300, $accrue->total_amount_actual);
        $this->assertEquals(1500, $accrue->total_amount_expense_estimated);
        $this->assertEquals(1300, $accrue->total_amount_expense_actual);
    }

    public function test_sync_performance_to_monthly_analysis(): void
    {
        // 1. Setup Project with PA
        $pa = ProfitabilityAnalysis::factory()->create();
        $project = Project::factory()->create(['profitability_analysis_id' => $pa->id]);

        // 2. Setup Monthly Performance record
        $monthName = now()->format('F');
        $year = (int) now()->format('Y');
        $monthly = ProfitabilityAnalysisMonthly::create([
            'profitability_analysis_id' => $pa->id,
            'month' => $monthName,
            'year' => $year,
            'actual_revenue' => 0,
        ]);

        // 3. Create Accrue Revenue for this project
        $accrue = AccrueRevenue::factory()->create([
            'project_id' => $project->id,
            'month' => (int) now()->format('n'),
            'year' => $year,
            'status' => AccrueRevenueStatus::Open,
        ]);

        AccrueRevenueItem::factory()->create([
            'accrue_revenue_id' => $accrue->id,
            'amount_actual' => 5000000,
        ]);

        // 4. Trigger sync
        $accrue->save();

        // 5. Assert sync happened
        $monthly->refresh();
        $this->assertEquals(5000000, $monthly->actual_revenue);
    }
}
