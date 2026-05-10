<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\CRM\Models\Customer;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\AccountMapping;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\JournalEntry;
use Modules\MasterData\Models\BankAccount;
use Modules\MasterData\Models\ProjectArea;
use Tests\TestCase;

class CashReceiptJournalTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => 'Test Customer',
        ]);

        $this->area = ProjectArea::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => 'Test Area',
            'code' => 'TA',
        ]);

        // Setup Accounts
        $this->arAccount = ChartOfAccount::create(['id' => \Illuminate\Support\Str::uuid(), 'code' => '110101', 'name' => 'AR', 'account_type' => 'Asset', 'is_active' => true]);
        $this->bankCOA = ChartOfAccount::create(['id' => \Illuminate\Support\Str::uuid(), 'code' => '110103', 'name' => 'Bank Mandiri', 'account_type' => 'Asset', 'is_active' => true]);

        // Setup Bank Account
        $this->bankAccount = BankAccount::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'bank_name' => 'Mandiri',
            'account_number' => '123456789',
            'account_name' => 'GDPS MAIN',
            'is_active' => true,
        ]);

        // Link Bank Account to COA
        $this->bankCOA->update([
            'accountable_id' => $this->bankAccount->id,
            'accountable_type' => BankAccount::class,
        ]);

        // Setup AR Mapping
        AccountMapping::create([
            'mappable_type' => Customer::class,
            'mappable_id' => $this->customer->id,
            'type' => 'receivable',
            'chart_of_account_id' => $this->arAccount->id,
        ]);
    }

    public function test_it_generates_cash_receipt_journal_when_invoice_is_paid(): void
    {
        // 1. Create Invoice in Sent status
        $invoice = Invoice::create([
            'customer_id' => $this->customer->id,
            'project_area_id' => $this->area->id,
            'total_amount' => 1000000,
            'amount' => 1000000,
            'invoice_date' => now(),
            'status' => InvoiceStatus::Sent,
            'bank_account_id' => $this->bankAccount->id,
            'number' => 'INV/TEST/001',
            'year' => date('Y'),
            'sequence_number' => 1,
        ]);

        // 2. Mark as Paid
        $invoice->update(['status' => InvoiceStatus::Paid]);

        // 3. Assertions - Header
        $this->assertDatabaseHas('journal_entries', [
            'reference_id' => $invoice->id,
            'reference_type' => Invoice::class,
            'total_amount' => 1000000,
        ]);

        $entry = JournalEntry::where('reference_id', $invoice->id)
            ->where('description', 'like', 'Penerimaan Kas%')
            ->first();

        $this->assertNotNull($entry);

        // 4. Assertions - Items
        // Debit Bank
        $this->assertDatabaseHas('journal_items', [
            'journal_entry_id' => $entry->id,
            'chart_of_account_id' => $this->bankCOA->id,
            'debit' => 1000000.00,
            'credit' => 0.00,
        ]);

        // Credit AR
        $this->assertDatabaseHas('journal_items', [
            'journal_entry_id' => $entry->id,
            'chart_of_account_id' => $this->arAccount->id,
            'debit' => 0.00,
            'credit' => 1000000.00,
        ]);
    }
}
