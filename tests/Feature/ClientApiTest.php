<?php

namespace Tests\Feature;

use App\Models\ApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClientApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_login_with_credentials()
    {
        $clientId = Str::random(32);
        $clientSecret = 'secret-password';

        ApiClient::create([
            'name' => 'Test Client',
            'client_id' => $clientId,
            'client_secret' => Hash::make($clientSecret),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/client/login', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'access_token',
                    'refresh_token',
                    'token_type',
                    'expires_in',
                ],
            ]);
    }

    public function test_client_cannot_login_with_invalid_secret()
    {
        $clientId = Str::random(32);
        $clientSecret = 'secret-password';

        ApiClient::create([
            'name' => 'Test Client',
            'client_id' => $clientId,
            'client_secret' => Hash::make($clientSecret),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/client/login', [
            'client_id' => $clientId,
            'client_secret' => 'wrong-password',
        ]);

        $response->dump();
        $response->assertStatus(422);
    }

    public function test_client_can_refresh_token()
    {
        $clientId = Str::random(32);
        $clientSecret = 'secret-password';

        $client = ApiClient::create([
            'name' => 'Test Client',
            'client_id' => $clientId,
            'client_secret' => Hash::make($clientSecret),
            'is_active' => true,
        ]);

        // Manually create a refresh token for the client
        $refreshToken = $client->createToken('refresh_token', ['issue-access-token'])->plainTextToken;

        $response = $this->postJson('/api/v1/client/refresh', [], [
            'Authorization' => 'Bearer '.$refreshToken,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'access_token',
                    'refresh_token',
                    'token_type',
                    'expires_in',
                ],
            ]);
    }

    public function test_client_can_access_protected_resource()
    {
        $client = ApiClient::create([
            'name' => 'Test Client',
            'client_id' => Str::random(32),
            'client_secret' => Hash::make('secret'),
            'is_active' => true,
        ]);

        $accessToken = $client->createToken('access_token', ['external:read'])->plainTextToken;

        $response = $this->getJson('/api/v1/external/projects', [
            'Authorization' => 'Bearer '.$accessToken,
        ]);

        $response->assertStatus(200);
    }
}
