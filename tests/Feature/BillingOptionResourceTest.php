<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Resources\BillingOptions\Pages\ListBillingOptions;
use Modules\MasterData\Models\BillingOption;
use Tests\TestCase;

class BillingOptionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_billing_options(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $billingOption = BillingOption::factory()->create([
            'name' => 'Test Billing Option',
        ]);

        Livewire::test(ListBillingOptions::class)
            ->assertCanSeeTableRecords([$billingOption])
            ->assertSee('Test Billing Option');
    }
}
