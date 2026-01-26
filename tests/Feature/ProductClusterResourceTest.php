<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Resources\ProductClusters\Pages\ListProductClusters;
use Modules\MasterData\Models\ProductCluster;
use Tests\TestCase;

class ProductClusterResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_product_clusters(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $productCluster = ProductCluster::factory()->create([
            'name' => 'Test Product Cluster',
        ]);

        Livewire::test(ListProductClusters::class)
            ->assertCanSeeTableRecords([$productCluster])
            ->assertSee('Test Product Cluster');
    }
}
