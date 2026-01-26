<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Resources\Items\Pages\ListItems;
use Modules\MasterData\Models\Item;
use Tests\TestCase;

class ItemResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_items(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create([
            'name' => 'Test Item',
        ]);

        Livewire::test(ListItems::class)
            ->assertCanSeeTableRecords([$item])
            ->assertSee('Test Item');
    }
}
