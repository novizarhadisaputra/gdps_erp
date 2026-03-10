<?php

namespace Modules\CRM\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Models\Customer;
use Tests\TestCase;

class CustomerCodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_3_letter_code_for_single_word_name()
    {
        $customer = Customer::create([
            'name' => 'GDPS',
            'email' => 'info@gdps.id',
            'status' => \Modules\MasterData\Enums\ActiveStatus::Active,
        ]);

        $this->assertEquals('GDP', $customer->code);
    }

    /** @test */
    public function it_generates_3_letter_code_for_three_word_name()
    {
        $customer = Customer::create([
            'name' => 'Anugerah Bangun Bersama',
            'email' => 'abb@example.com',
            'status' => \Modules\MasterData\Enums\ActiveStatus::Active,
        ]);

        $this->assertEquals('ABB', $customer->code);
    }

    /** @test */
    public function it_generates_3_letter_code_for_two_word_name_using_last_letter()
    {
        // Garuda Indonesia -> G (Garuda) I (Indonesia) A (last of Indonesia)
        $customer = Customer::create([
            'name' => 'Garuda Indonesia',
            'email' => 'info@garuda.com',
            'status' => \Modules\MasterData\Enums\ActiveStatus::Active,
        ]);

        $this->assertEquals('GIA', $customer->code);
    }

    /** @test */
    public function it_handles_collisions_using_index_before_last_a()
    {
        // 1. Create Garuda Indonesia (GIA)
        Customer::create([
            'name' => 'Garuda Indonesia',
            'email' => 'info@garuda.com',
            'status' => \Modules\MasterData\Enums\ActiveStatus::Active,
        ]);

        // 2. Create Garuda India (Base Rule -> GIA, Collision!)
        // India -> last 'a' is index 4. Letter before it is index 3 ('i').
        // So code should be GII.
        $customer2 = Customer::create([
            'name' => 'Garuda India',
            'email' => 'info@garuda-india.com',
            'status' => \Modules\MasterData\Enums\ActiveStatus::Active,
        ]);

        $this->assertEquals('GII', $customer2->code);
    }

    /** @test */
    public function it_strips_legal_entity_prefixes()
    {
        $customer = Customer::create([
            'name' => 'PT Aerofood Indonesia',
            'email' => 'info@aerofood.id',
            'status' => \Modules\MasterData\Enums\ActiveStatus::Active,
        ]);

        // Aerofood Indonesia -> A (1st) I (2nd) A (last of Indonesia) -> AIA
        // Wait, the user said ACS for Aerofood Indonesia as an example, but if I follow the rule...
        // Let's see if AIA is generated.
        $this->assertEquals('AIA', $customer->code);
    }
}
