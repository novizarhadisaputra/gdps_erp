<?php

namespace Modules\MasterData\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Modules\MasterData\Models\Unit;

class UnitService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'https://staffing.garudapratama.com/api/v1/units';
    }

    /**
     * Fetch units from the external API.
     */
    public function getUnits(int $page = 1, int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $accessToken = null;

        if ($user && $user instanceof \App\Models\User) {
            // Check if token needs refresh
            if ($user->needsTokenRefresh() && $user->refresh_token) {
                try {
                    $ssoService = app(\App\Services\SsoAuthService::class);
                    $tokenData = $ssoService->refreshAccessToken($user->refresh_token);

                    $user->update([
                        'access_token' => $tokenData['access_token'],
                        'refresh_token' => $tokenData['refresh_token'] ?? $user->refresh_token,
                        'token_expires_at' => now()->addSeconds($tokenData['expires_in']),
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to refresh token in UnitService', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            $accessToken = $user->access_token;
        }

        $request = Http::asJson();
        if ($accessToken) {
            $request->withToken($accessToken);
        }

        $response = $request->get($this->baseUrl, [
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search,
        ]);

        if ($response->failed()) {
            \Illuminate\Support\Facades\Log::error('API Request failed in UnitService', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return new LengthAwarePaginator([], 0, $perPage, $page);
        }

        $data = $response->json();

        if (! ($data['success'] ?? true)) {
            \Illuminate\Support\Facades\Log::warning('API returned success=false in UnitService', [
                'message' => $data['message'] ?? 'No error message provided',
                'data' => $data,
            ]);

            return new LengthAwarePaginator([], 0, $perPage, $page);
        }

        // Assuming the API returns a standard Laravel pagination structure or similar
        // Adjust these keys based on actual API response
        $items = collect($data['data'] ?? [])->map(function ($item) {
            return new Unit([
                'id' => $item['id'] ?? $item['ORGANISASI_ID'] ?? null,
                'code' => $item['code'] ?? $item['ORGANISASI_CODE'] ?? null,
                'name' => $item['name'] ?? $item['ORGANISASI_NAMA'] ?? null,
                'description' => $item['description'] ?? null,
                'is_active' => $item['is_active'] ?? true,
            ]);
        });

        return new LengthAwarePaginator(
            $items,
            $data['total'] ?? $data['meta']['total'] ?? $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
