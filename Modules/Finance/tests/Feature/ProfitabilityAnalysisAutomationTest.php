<?php

namespace Modules\Finance\Tests\Feature;

use Tests\TestCase;

class ProfitabilityAnalysisAutomationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
    }
}
