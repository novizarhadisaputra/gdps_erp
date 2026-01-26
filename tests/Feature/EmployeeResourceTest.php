<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Resources\Employees\Pages\ListEmployees;
use Modules\MasterData\Models\Employee;
use Tests\TestCase;

class EmployeeResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_employees(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $employee = Employee::factory()->create([
            'name' => 'Test Employee',
        ]);

        Livewire::test(ListEmployees::class)
            ->assertCanSeeTableRecords([$employee])
            ->assertSee('Test Employee');
    }
}
