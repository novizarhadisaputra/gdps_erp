<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Project\Filament\Resources\ProjectInformations\Pages\ListProjectInformations;
use Modules\Project\Models\ProjectInformation;
use Tests\TestCase;

class ProjectInformationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_project_informations(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $info = ProjectInformation::factory()->create();

        Livewire::test(ListProjectInformations::class)
            ->assertCanSeeTableRecords([$info]);
    }
}
