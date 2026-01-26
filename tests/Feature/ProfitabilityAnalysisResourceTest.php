<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\ListProfitabilityAnalyses;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Tests\TestCase;

class ProfitabilityAnalysisResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_profitability_analyses(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $pa = ProfitabilityAnalysis::factory()->create();

        Livewire::test(ListProfitabilityAnalyses::class)
            ->assertCanSeeTableRecords([$pa]);
    }
}
