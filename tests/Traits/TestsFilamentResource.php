<?php

namespace Tests\Traits;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;

trait TestsFilamentResource
{
    /**
     * Get the resource class being tested.
     */
    abstract protected function getResource(): string;

    /**
     * Get valid input data for creating/updating a record.
     */
    abstract protected function getValidInput(?Model $record = null): array;

    protected function setUp(): void
    {
        parent::setUp();

        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'web');

        // Bypass all permissions for testing
        \Illuminate\Support\Facades\Gate::before(fn () => true);

        // Ensure Filament panel is set for the test
        try {
            \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));
        } catch (\Exception $e) {
            // Fallback
        }
    }

    public function test_can_render_index_page(): void
    {
        $this->withoutMiddleware([\Filament\Http\Middleware\Authenticate::class])
            ->get($this->getResource()::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_render_create_page(): void
    {
        if (! isset($this->getResource()::getPages()['create'])) {
            $this->markTestSkipped('Resource does not have a dedicated Create page.');
        }

        $this->withoutMiddleware([\Filament\Http\Middleware\Authenticate::class])
            ->get($this->getResource()::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_can_render_edit_page(): void
    {
        if (! isset($this->getResource()::getPages()['edit'])) {
            $this->markTestSkipped('Resource does not have a dedicated Edit page.');
        }

        $model = $this->getResource()::getModel();
        $record = $model::factory()->create();

        $this->withoutMiddleware([\Filament\Http\Middleware\Authenticate::class])
            ->get($this->getResource()::getUrl('edit', ['record' => $record]))
            ->assertSuccessful();
    }

    public function test_can_list_records(): void
    {
        $model = $this->getResource()::getModel();
        $records = $model::factory()->count(5)->create();

        Livewire::test($this->getResource()::getPages()['index']->getPage())
            ->assertCanSeeTableRecords($records);
    }

    public function test_can_create_record(): void
    {
        $input = $this->getValidInput();

        if (isset($this->getResource()::getPages()['create'])) {
            Livewire::test($this->getResource()::getPages()['create']->getPage())
                ->fillForm($input)
                ->call('create')
                ->assertHasNoFormErrors();
        } else {
            // Test Create Modal on Index Page
            Livewire::test($this->getResource()::getPages()['index']->getPage())
                ->mountAction('create')
                ->fillForm($input)
                ->callMountedAction()
                ->assertHasNoFormErrors();
        }

        // Sanitize input to only include actual database columns (simple values)
        $databaseData = collect($input)->filter(fn ($value) => ! is_array($value))->toArray();
        $this->assertDatabaseHas($this->getResource()::getModel(), $databaseData);
    }

    public function test_can_edit_record(): void
    {
        $model = $this->getResource()::getModel();
        $record = $model::factory()->create();
        $input = $this->getValidInput($record);

        if (isset($this->getResource()::getPages()['edit'])) {
            Livewire::test($this->getResource()::getPages()['edit']->getPage(), ['record' => $record->getRouteKey()])
                ->fillForm($input)
                ->call('save')
                ->assertHasNoFormErrors();
        } else {
            // Test Edit Action on Index Page (Table Action)
            Livewire::test($this->getResource()::getPages()['index']->getPage())
                ->mountTableAction('edit', $record)
                ->fillForm($input)
                ->callMountedTableAction()
                ->assertHasNoFormErrors();
        }

        // Sanitize input
        $databaseData = collect($input)->filter(fn ($value) => ! is_array($value))->toArray();
        $this->assertDatabaseHas($this->getResource()::getModel(), array_merge(
            ['id' => $record->id],
            $databaseData
        ));
    }

    public function test_can_delete_record(): void
    {
        $model = $this->getResource()::getModel();
        $record = $model::factory()->create();

        if (isset($this->getResource()::getPages()['edit'])) {
            Livewire::test($this->getResource()::getPages()['edit']->getPage(), ['record' => $record->getRouteKey()])
                ->callAction('delete');
        } else {
            // Test Delete Action on Index Page (Table Action)
            Livewire::test($this->getResource()::getPages()['index']->getPage())
                ->mountTableAction('delete', $record)
                ->callMountedTableAction();
        }

        $this->assertDatabaseMissing($this->getResource()::getModel(), [
            'id' => $record->id,
        ]);
    }

    public function test_can_delete_record_from_table(): void
    {
        if (! isset($this->getResource()::getPages()['index'])) {
            $this->markTestSkipped('Resource does not have an Index page.');
        }

        $model = $this->getResource()::getModel();
        $record = $model::factory()->create();

        Livewire::test($this->getResource()::getPages()['index']->getPage())
            ->mountTableAction('delete', $record)
            ->callMountedTableAction();

        $this->assertDatabaseMissing($this->getResource()::getModel(), [
            'id' => $record->id,
        ]);
    }
}
