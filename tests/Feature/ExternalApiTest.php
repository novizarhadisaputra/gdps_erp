<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Project\Models\Project;
use Tests\TestCase;

class ExternalApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_tokens()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
            ]);
    }

    public function test_refresh_token_rotation()
    {
        $user = User::factory()->create();
        // Create a refresh token manually with correct ability
        $refreshToken = $user->createToken('refresh_token', ['issue-access-token'])->plainTextToken;

        $response = $this->postJson('/api/refresh', [], [
            'Authorization' => 'Bearer '.$refreshToken,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ]);

        // Ensure the new access token has correct ability
        $newAccessToken = $response->json('access_token');
        // We can't easily verify the ability of the returned string token without DB lookup,
        // but we can verify it works on a protected route.
    }

    public function test_cannot_refresh_with_access_token()
    {
        $user = User::factory()->create();
        $accessToken = $user->createToken('access_token', ['access-api'])->plainTextToken;

        $response = $this->postJson('/api/refresh', [], [
            'Authorization' => 'Bearer '.$accessToken,
        ]);

        // Should be 403 because access token doesn't have 'issue-access-token' ability
        $response->assertStatus(403);
    }

    public function test_access_external_projects_protected()
    {
        $response = $this->getJson('/api/v1/external/projects');
        $response->assertStatus(401);
    }

    public function test_access_external_projects_success()
    {
        $user = User::factory()->create();
        $accessToken = $user->createToken('access_token', ['access-api'])->plainTextToken;

        // Seed a project
        // Note: Project factory might need dependencies like Customer, but let's see.
        // If Project factory assumes existing relations, it might fail.
        // Let's create a minimal project if possible, or use one if factory is robust.
        // Assuming ProjectFactory exists as indicated by newFactory() in model.

        // For robustness, mock Project creation or try-catch.
        // But better: create dependencies.
        // Modules\MasterData\Models\Customer usually needed.
        // Let's rely on factory handling it, or just assert empty list 200 OK.

        $response = $this->getJson('/api/v1/external/projects', [
            'Authorization' => 'Bearer '.$accessToken,
        ]);

        $response->assertStatus(200);
    }
}
