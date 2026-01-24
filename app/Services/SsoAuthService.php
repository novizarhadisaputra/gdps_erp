<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SsoAuthService
{
    /**
     * Authenticate user with SSO API.
     *
     * @throws RequestException
     */
    public function login(string $email, string $password): array
    {
        try {
            $response = Http::asForm()
                ->withHeaders([
                    'Authorization' => 'Basic Z2Rwcy1zcGFjZS1hcHA6MzlhYWRhNTEtNGZlMC00MDk4LTg2NTctNTk3OTg5YTI4ZTA4',
                ])
                ->post(config('services.sso.auth_url'), [
                    'email' => $email,
                    'password' => $password,
                ]);

            $data = $response->json();

            if ($response->failed() || ! ($data['success'] ?? false)) {
                throw new \Exception($data['message'] ?? 'SSO authentication failed');
            }

            return $data['data'];
        } catch (\Exception $e) {
            Log::error('SSO login failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get employee data from Staffing API.
     *
     * @throws RequestException
     */
    public function getEmployeeData(string $email, string $accessToken): array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get(config('services.sso.staffing_url'), [
                    'email' => $email,
                ]);

            $data = $response->json();

            if ($response->failed() || ! ($data['success'] ?? false)) {
                throw new \Exception($data['message'] ?? 'Failed to retrieve employee data');
            }

            return $data['data'];
        } catch (\Exception $e) {
            Log::error('Failed to retrieve employee data', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Refresh access token using refresh token.
     *
     * @throws RequestException
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        try {
            $response = Http::asForm()
                ->post(config('services.sso.refresh_url'), [
                    'refresh_token' => $refreshToken,
                ]);

            $data = $response->json();

            if ($response->failed() || ! ($data['success'] ?? false)) {
                throw new \Exception($data['message'] ?? 'Token refresh failed');
            }

            return $data['data'];
        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
