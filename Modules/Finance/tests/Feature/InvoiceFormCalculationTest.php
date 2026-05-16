<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Pages\EditInvoice;
use Modules\Finance\Models\Invoice;
use Modules\MasterData\Models\RevenueType;
use Modules\MasterData\Models\Tax;
use Tests\TestCase;

class InvoiceFormCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Gate::before(fn () => true);

        // Ensure super_admin role exists for Filament access
        $roleId = (string) Str::uuid();
        DB::table('roles')->insert([
            'id' => $roleId,
            'name' => 'super_admin',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        // Seed necessary data
        Tax::create([
            'name' => 'PPN 12%',
            'category' => 'sales',
            'rate' => 12,
            'is_active' => true,
        ]);

        RevenueType::create([
            'name' => 'Main Revenue',
            'code' => 'main',
            'is_active' => true,
        ]);

        RevenueType::create([
            'name' => 'Additional Revenue',
            'code' => 'additional',
            'is_active' => true,
        ]);
    }

    public function test_invoice_recalculates_totals_dynamically_from_items(): void
    {
        $invoice = Invoice::factory()->create();

        // Use set() to ensure reactive updates are triggered
        Livewire::test(EditInvoice::class, [
            'record' => $invoice->getRouteKey(),
        ])
            ->set('data.items', [
                'uuid1' => ['quantity' => 2, 'unit_price' => 1000],
                'uuid2' => ['quantity' => 1, 'unit_price' => 500],
            ])
            ->assertFormSet([
                'amount' => 2500,
            ]);
    }

    public function test_invoice_resolves_management_fee_basis_from_items_containing_fee_text(): void
    {
        $invoice = Invoice::factory()->create();

        Livewire::test(EditInvoice::class, [
            'record' => $invoice->getRouteKey(),
        ])
            ->set('data.items', [
                'uuid1' => ['quantity' => 1, 'unit_price' => 2000, 'item_name' => 'Main Service'],
                'uuid2' => ['quantity' => 1, 'unit_price' => 500, 'item_name' => 'Management Fee'],
            ])
            ->set('data.tax_basis', 'management_fee')
            ->assertFormSet([
                'tax_base_amount' => 500,
            ]);
    }

    public function test_invoice_resolves_management_fee_basis_from_snapshot(): void
    {
        $invoice = Invoice::factory()->create();
        $snapshot = [
            'summary' => [
                'total_price' => 1000,
                'total_cost' => 850,
            ],
        ];

        Livewire::test(EditInvoice::class, [
            'record' => $invoice->getRouteKey(),
        ])
            ->set('data.snapshot', $snapshot)
            ->set('data.items', [
                'uuid1' => ['quantity' => 1, 'unit_price' => 1000, 'item_name' => 'Combined Item'],
            ])
            ->set('data.tax_basis', 'management_fee')
            ->assertFormSet([
                'tax_base_amount' => 150, // 1000 - 850
            ]);
    }
}
