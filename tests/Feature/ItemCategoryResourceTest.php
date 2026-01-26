<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Resources\ItemCategories\Pages\ListItemCategories;
use Modules\MasterData\Models\ItemCategory;
use Tests\TestCase;

class ItemCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_item_categories(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = ItemCategory::factory()->create([
            'name' => 'Test Category',
        ]);

        Livewire::test(ListItemCategories::class)
            ->assertCanSeeTableRecords([$category])
            ->assertSee('Test Category');
    }
}
