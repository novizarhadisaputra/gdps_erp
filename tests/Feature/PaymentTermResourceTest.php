<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Resources\PaymentTerms\Pages\ListPaymentTerms;
use Modules\MasterData\Models\PaymentTerm;
use Tests\TestCase;

class PaymentTermResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_payment_terms(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $paymentTerm = PaymentTerm::factory()->create([
            'name' => 'Test Payment Term',
        ]);

        Livewire::test(ListPaymentTerms::class)
            ->assertCanSeeTableRecords([$paymentTerm])
            ->assertSee('Test Payment Term');
    }
}
