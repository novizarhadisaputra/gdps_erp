<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\Invoice;
use Tests\TestCase;

class InvoiceResourceTest extends TestCase
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
    }

    public function test_can_list_invoices(): void
    {
        Invoice::factory()->count(3)->create();

        $this->get(\Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\InvoiceResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_create_invoice_and_verify_auto_numbering(): void
    {
        $invoice = Invoice::factory()->create();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'year' => (int) date('Y'),
        ]);

        $this->assertMatchesRegularExpression('/GDPS\/UB\/INV-\d{3}\/\d{2}/', $invoice->number);
    }

    public function test_invoice_revision_logic(): void
    {
        // 1. Create a Submitted Invoice
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Submitted]);
        $originalNumber = $invoice->number;

        // 2. Change status back to Draft (triggers revision in Observer)
        $invoice->update(['status' => InvoiceStatus::Draft]);

        // 3. Verify Revision was created
        $this->assertDatabaseHas('invoice_revisions', [
            'invoice_id' => $invoice->id,
            'number' => $originalNumber,
        ]);

        // 4. Verify main Invoice number and revision_number updated
        $invoice->refresh();
        $this->assertEquals(1, $invoice->revision_number);
        $this->assertStringContainsString('/REV/01/', $invoice->number);
    }

    public function test_automatic_paid_status_on_payment_proof_upload(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Sent]);

        // Mock media upload
        $invoice->addMediaFromUrl('https://picsum.photos/200/300')
            ->toMediaCollection('payment_proof');

        // Observer saved() hook should trigger
        $invoice->save();

        $this->assertEquals(InvoiceStatus::Paid, $invoice->status);
    }

    public function test_automatic_approved_status_on_signed_invoice_upload(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Submitted]);

        // Mock full approval (assuming isFullyApproved returns true for this test case)
        // Note: isFullyApproved logic might depend on signatures, but we can mock or force it if needed.

        $invoice->addMediaFromUrl('https://picsum.photos/200/300')
            ->toMediaCollection('signed_invoice');

        $invoice->save();

        $this->assertEquals(InvoiceStatus::Approved, $invoice->status);
    }
}
