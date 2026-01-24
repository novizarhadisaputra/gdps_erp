<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class CustomLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_sso_login_creates_new_user(): void
    {
        Http::fake([
            config('services.sso.auth_url') => Http::response([
                'success' => true,
                'data' => [
                    'accessToken' => 'test-access-token',
                    'refreshToken' => 'test-refresh-token',
                    'expiresIn' => 900,
                ],
            ]),
            config('services.sso.staffing_url').'*' => Http::response([
                'success' => true,
                'data' => [
                    'NOPEG' => '9500154',
                    'NAMA' => 'Test User',
                    'PERUSAHAAN' => 'Test Company',
                    'ORGANISASI_ID' => '10000014',
                    'ORGANISASI' => 'Test Unit',
                    'POSISI_ID' => '10000299',
                    'POSISI' => 'Test Position',
                    'EMAIL' => 'test@example.com',
                    'MOBILE_PHONE' => '62895374891022',
                ],
            ]),
        ]);

        Livewire::test(Login::class)
            ->set('data.email', 'test@example.com')
            ->set('data.password', 'password123')
            ->call('authenticate')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'employee_code' => '9500154',
            'name' => 'Test User',
            'company' => 'Test Company',
            'unit_id' => '10000014',
            'unit' => 'Test Unit',
            'position_id' => '10000299',
            'position' => 'Test Position',
            'mobile_phone' => '62895374891022',
        ]);

        $this->assertAuthenticated();
    }

    public function test_successful_sso_login_updates_existing_user(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Old Name',
            'employee_code' => '9500154',
        ]);

        Http::fake([
            config('services.sso.auth_url') => Http::response([
                'success' => true,
                'data' => [
                    'accessToken' => 'test-access-token',
                    'refreshToken' => 'test-refresh-token',
                    'expiresIn' => 900,
                ],
            ]),
            config('services.sso.staffing_url').'*' => Http::response([
                'success' => true,
                'data' => [
                    'NOPEG' => '9500154',
                    'NAMA' => 'Updated Name',
                    'PERUSAHAAN' => 'Test Company',
                    'ORGANISASI_ID' => '10000014',
                    'ORGANISASI' => 'Test Unit',
                    'POSISI_ID' => '10000299',
                    'POSISI' => 'Test Position',
                    'EMAIL' => 'test@example.com',
                    'MOBILE_PHONE' => '62895374891022',
                ],
            ]),
        ]);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'test@example.com',
                'password' => 'password123',
            ])
            ->call('authenticate');

        $user->refresh();

        $this->assertEquals('Updated Name', $user->name);
        $this->assertNotNull($user->access_token);
        $this->assertNotNull($user->refresh_token);
        $this->assertNotNull($user->token_expires_at);
    }

    public function test_tokens_are_encrypted_in_database(): void
    {
        Http::fake([
            config('services.sso.auth_url') => Http::response([
                'success' => true,
                'data' => [
                    'accessToken' => 'test-access-token',
                    'refreshToken' => 'test-refresh-token',
                    'expiresIn' => 900,
                ],
            ]),
            config('services.sso.staffing_url').'*' => Http::response([
                'success' => true,
                'data' => [
                    'NOPEG' => '9500154',
                    'NAMA' => 'Test User',
                    'PERUSAHAAN' => 'Test Company',
                    'ORGANISASI_ID' => '10000014',
                    'ORGANISASI' => 'Test Unit',
                    'POSISI_ID' => '10000299',
                    'POSISI' => 'Test Position',
                    'EMAIL' => 'test@example.com',
                    'MOBILE_PHONE' => '62895374891022',
                ],
            ]),
        ]);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'test@example.com',
                'password' => 'password123',
            ])
            ->call('authenticate');

        $user = User::where('email', 'test@example.com')->first();

        // Tokens should be decrypted when accessed via model
        $this->assertEquals('test-access-token', $user->access_token);
        $this->assertEquals('test-refresh-token', $user->refresh_token);

        // Raw database value should be encrypted (not equal to plain text)
        $rawUser = \DB::table('users')->where('email', 'test@example.com')->first();
        $this->assertNotEquals('test-access-token', $rawUser->access_token);
        $this->assertNotEquals('test-refresh-token', $rawUser->refresh_token);
    }

    public function test_failed_authentication_shows_error(): void
    {
        Http::fake([
            config('services.sso.auth_url') => Http::response([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401),
        ]);

        Livewire::test(Login::class)
            ->set('data.email', 'test@example.com')
            ->set('data.password', 'wrong-password')
            ->call('authenticate')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_network_error_shows_error(): void
    {
        Http::fake([
            config('services.sso.auth_url') => Http::response(null, 500),
        ]);

        Livewire::test(Login::class)
            ->set('data.email', 'test@example.com')
            ->set('data.password', 'password123')
            ->call('authenticate')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }
}
