<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Tests\TestCase;

class ProfitabilityAnalysisResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_view_nested_profitability_analysis_resource(): void
    {
        $role = $roleId = (string) \Illuminate\Support\Str::uuid();

        $roleId = (string) \Illuminate\Support\Str::uuid();
        \Illuminate\Support\Facades\DB::table('roles')->insert([
            'id' => $roleId,
            'name' => 'super_admin',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = \App\Models\User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);
        $lead = Lead::factory()->create();

        // PA depends on General Information
        $gi = GeneralInformation::factory()->create(['lead_id' => $lead->id]);

        $this->get(\Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource::getUrl('index', ['lead' => $lead->id]))
            ->assertSuccessful()
            ->assertSee('Profitability Analyses');
    }
}
