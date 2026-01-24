<?php

namespace Tests\Unit;

use App\Services\SsoAuthService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SsoAuthServiceTest extends TestCase
{
    protected SsoAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SsoAuthService;
    }

    public function test_login_successful(): void
    {
        Http::fake([
            config('services.sso.auth_url') => Http::response([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'accessToken' => 'test-access-token',
                    'refreshToken' => 'test-refresh-token',
                    'expiresIn' => 900,
                ],
            ], 200),
        ]);

        $result = $this->service->login('test@example.com', 'password');

        $this->assertEquals('test-access-token', $result['accessToken']);
        $this->assertEquals('test-refresh-token', $result['refreshToken']);
        $this->assertEquals(900, $result['expiresIn']);
    }

    public function test_login_failed_with_invalid_credentials(): void
    {
        Http::fake([
            config('services.sso.auth_url') => Http::response([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->service->login('test@example.com', 'wrong-password');
    }

    public function test_get_employee_data_successful(): void
    {
        Http::fake([
            config('services.sso.staffing_url').'*' => Http::response([
                'success' => true,
                'message' => 'Data retrieved successfully',
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
            ], 200),
        ]);

        $result = $this->service->getEmployeeData('test@example.com', 'test-token');

        $this->assertEquals('9500154', $result['NOPEG']);
        $this->assertEquals('Test User', $result['NAMA']);
        $this->assertEquals('Test Company', $result['PERUSAHAAN']);
    }

    public function test_get_employee_data_failed(): void
    {
        Http::fake([
            config('services.sso.staffing_url').'*' => Http::response([
                'success' => false,
                'message' => 'Employee not found',
            ], 404),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Employee not found');

        $this->service->getEmployeeData('test@example.com', 'test-token');
    }

    public function test_refresh_access_token_successful(): void
    {
        Http::fake([
            config('services.sso.refresh_url') => Http::response([
                'success' => true,
                'message' => 'Token refreshed',
                'data' => [
                    'accessToken' => 'new-access-token',
                    'refreshToken' => 'new-refresh-token',
                    'expiresIn' => 900,
                ],
            ], 200),
        ]);

        $result = $this->service->refreshAccessToken('old-refresh-token');

        $this->assertEquals('new-access-token', $result['accessToken']);
        $this->assertEquals('new-refresh-token', $result['refreshToken']);
    }

    public function test_refresh_access_token_failed(): void
    {
        Http::fake([
            config('services.sso.refresh_url') => Http::response([
                'success' => false,
                'message' => 'Invalid refresh token',
            ], 401),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid refresh token');

        $this->service->refreshAccessToken('invalid-token');
    }
}
