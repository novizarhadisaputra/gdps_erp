<?php

namespace Tests\Feature\Modules\MasterData\tests\Feature;

use Tests\TestCase;

class ApprovalNotificationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
