<?php

namespace Modules\MasterData\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Models\Customer;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\Tax;
use Tests\TestCase;

class MasterDataObserverTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_unique_abbreviation_for_customer_with_empty_code(): void
    {
        $customer = Customer::create([
            'name' => 'Garuda Indonesia',
            'email' => 'info@garuda.co.id',
        ]);

        $this->assertNotEmpty($customer->code);
        // "Garuda Indonesia" -> G(aruda) + I(ndonesia) + A(last char of Indonesia)
        $this->assertEquals('GIA', $customer->code);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_unique_abbreviation_for_single_word_name(): void
    {
        $tax = Tax::create([
            'name' => 'Taxable',
            'is_active' => true,
        ]);

        $this->assertNotEmpty($tax->code);
        // "Taxable" -> "TAX" (first 3 letters)
        $this->assertEquals('TAX', $tax->code);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_duplicates_by_decrementing_last_word_index(): void
    {
        // Create first customer
        $customer1 = Customer::create([
            'name' => 'Garuda Indonesia',
            'email' => 'garuda1@test.com',
        ]);

        // Create second customer - should decrement index from "Indonesia"
        $customer2 = Customer::create([
            'name' => 'Garuda Indonesia',
            'email' => 'garuda2@test.com',
        ]);

        // Create third customer
        $customer3 = Customer::create([
            'name' => 'Garuda Indonesia',
            'email' => 'garuda3@test.com',
        ]);

        // First gets original (last char), subsequent decrement backwards through word
        $this->assertEquals('GIA', $customer1->code); // Indonesi[a]
        $this->assertEquals('GII', $customer2->code); // Indones[i]a
        $this->assertEquals('GIS', $customer3->code); // Indone[s]ia

        // All should be exactly 3 characters
        $this->assertEquals(3, strlen($customer1->code));
        $this->assertEquals(3, strlen($customer2->code));
        $this->assertEquals(3, strlen($customer3->code));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_override_manually_set_code(): void
    {
        $cluster = ProductCluster::create([
            'code' => 'CUSTOM',
            'name' => 'Building Cleaning Apartment',
            'is_active' => true,
        ]);

        $this->assertEquals('CUSTOM', $cluster->code);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_converts_abbreviation_to_uppercase(): void
    {
        $customer = Customer::create([
            'name' => 'small letters company',
            'email' => 'test@test.com',
        ]);

        $this->assertEquals(strtoupper($customer->code), $customer->code);
    }
}
