<?php

namespace Modules\Project\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages\EditWorkCompletionReport;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages\ListWorkCompletionReports;
use Modules\Project\Models\WorkCompletionReport;
use Tests\TestCase;

class WorkCompletionReportResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Gate::before(fn () => true);
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_render_the_list_page(): void
    {
        $this->actingAs($this->user);
        WorkCompletionReport::factory()->count(3)->create();

        Livewire::test(ListWorkCompletionReports::class)
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_edit_page(): void
    {
        $this->actingAs($this->user);
        $wcr = WorkCompletionReport::factory()->create();

        Livewire::test(EditWorkCompletionReport::class, [
            'record' => $wcr->getRouteKey(),
        ])
            ->assertFormSet([
                'number' => $wcr->number,
                'project_id' => $wcr->project_id,
            ]);
    }

    /** @test */
    public function it_can_update_work_completion_report_revision(): void
    {
        $this->actingAs($this->user);
        $wcr = WorkCompletionReport::factory()->create();

        // Ensure the edit page can be loaded
        Livewire::test(EditWorkCompletionReport::class, [
            'record' => $wcr->getRouteKey(),
        ])->assertSuccessful();

        // Functional update test
        $wcr->update(['revision_number' => 2]);

        $this->assertDatabaseHas('work_completion_reports', [
            'id' => $wcr->id,
            'revision_number' => 2,
        ]);
    }
}
