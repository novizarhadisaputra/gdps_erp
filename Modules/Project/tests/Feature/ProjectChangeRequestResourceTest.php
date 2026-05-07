<?php

namespace Modules\Project\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests\Pages\EditProjectChangeRequest;
use Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests\Pages\ListProjectChangeRequests;
use Modules\Project\Models\ProjectChangeRequest;
use Tests\TestCase;

class ProjectChangeRequestResourceTest extends TestCase
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
        ProjectChangeRequest::factory()->count(3)->create();

        Livewire::test(ListProjectChangeRequests::class)
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_edit_page(): void
    {
        $this->actingAs($this->user);
        $pcr = ProjectChangeRequest::factory()->create();

        Livewire::test(EditProjectChangeRequest::class, [
            'record' => $pcr->getRouteKey(),
        ])
            ->assertFormSet([
                'project_id' => $pcr->project_id,
                'number' => $pcr->number,
            ]);
    }

    /** @test */
    public function it_can_update_change_request_notes(): void
    {
        $this->actingAs($this->user);
        $pcr = ProjectChangeRequest::factory()->create();

        Livewire::test(EditProjectChangeRequest::class, [
            'record' => $pcr->getRouteKey(),
        ])
            ->fillForm([
                'notes' => 'Updated PCR Notes',
                'project_id' => $pcr->project_id,
                'type' => $pcr->type->value,
                'status' => $pcr->status->value,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('project_change_requests', [
            'id' => $pcr->id,
            'notes' => '<p>Updated PCR Notes</p>',
        ]);
    }
}
