<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Models\BpjsConfig;
use Tests\TestCase;

class BpjsConfigFormTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_bpjs_config_with_valid_data()
    {
        $config = BpjsConfig::create([
            'name' => 'Test BPJS Config',
            'type' => 'employment',
            'category' => 'JKK',
            'employer_rate' => 0.0024,
            'employee_rate' => 0.0,
            'risk_level' => 'very_low',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('bpjs_configs', [
            'name' => 'Test BPJS Config',
            'type' => 'employment',
            'category' => 'JKK',
            'risk_level' => 'very_low',
        ]);

        $this->assertEquals(0.0024, (float) $config->employer_rate);
    }
}
