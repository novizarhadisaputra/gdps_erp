<?php

namespace Modules\MasterData\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Models\Client;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\Tax;
use Tests\TestCase;

class MasterDataObserverTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_unique_abbreviation_for_client_with_empty_code(): void
    {
        $client = Client::create([
            'name' => 'Garuda Indonesia',
            'email' => 'info@garuda.co.id',
        ]);

        $this->assertNotEmpty($client->code);
        // "Garuda Indonesia" -> G(aruda) + I(ndonesia) + A(last char of Indonesia)
        $this->assertEquals('GIA', $client->code);
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
        // Create first client
        $client1 = Client::create([
            'name' => 'Garuda Indonesia',
            'email' => 'garuda1@test.com',
        ]);

        // Create second client - should decrement index from "Indonesia"
        $client2 = Client::create([
            'name' => 'Garuda Indonesia',
            'email' => 'garuda2@test.com',
        ]);

        // Create third client
        $client3 = Client::create([
            'name' => 'Garuda Indonesia',
            'email' => 'garuda3@test.com',
        ]);

        // First gets original (last char), subsequent decrement backwards through word
        $this->assertEquals('GIA', $client1->code); // Indonesi[a]
        $this->assertEquals('GII', $client2->code); // Indones[i]a
        $this->assertEquals('GIS', $client3->code); // Indone[s]ia

        // All should be exactly 3 characters
        $this->assertEquals(3, strlen($client1->code));
        $this->assertEquals(3, strlen($client2->code));
        $this->assertEquals(3, strlen($client3->code));
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
        $client = Client::create([
            'name' => 'small letters company',
            'email' => 'test@test.com',
        ]);

        $this->assertEquals(strtoupper($client->code), $client->code);
    }
}
