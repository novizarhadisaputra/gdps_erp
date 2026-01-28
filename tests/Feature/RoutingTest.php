<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_login_for_guests()
    {
        $response = $this->get('/');

        // Filament usually redirects to /login if unauthenticated
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_login_route_exists()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_dashboard_at_root()
    {
        $user = User::factory()->create();

        // Assuming dashboard is at '/' now
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
    }
}
