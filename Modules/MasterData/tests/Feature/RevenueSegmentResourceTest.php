<?php

namespace Modules\MasterData\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\Unit;
use Tests\TestCase;

class RevenueSegmentResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_list_revenue_segments_without_sql_error(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Grant permission only for this test if needed to see columns,
        // but for basic listing, we just want to ensure it doesn't crash.
        Gate::before(fn () => true);

        $revenueSegment = RevenueSegment::factory()->create([
            'name' => 'Test Revenue Segment',
        ]);

        $url = route('filament.admin.master-data.resources.revenue-segments.index');

        $this->get($url)
            ->assertSuccessful()
            ->assertSee('Test Revenue Segment');
    }

    public function test_revenue_segments_are_not_scoped_by_unit(): void
    {
        $unit1 = Unit::factory()->create();
        $unit2 = Unit::factory()->create();

        $user1 = User::factory()->create(['unit_id' => $unit1->id]);

        $segment1 = RevenueSegment::factory()->create(['name' => 'Segment 1']);
        $segment2 = RevenueSegment::factory()->create(['name' => 'Segment 2']);

        $this->actingAs($user1);

        // Test that all records are visible regardless of unit (since it's global)
        $records = RevenueSegment::all();
        $this->assertCount(2, $records);
    }
}
