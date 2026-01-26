<?php

namespace Modules\MasterData\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\MasterData\Models\Unit;

class UnitService
{
    public const CACHE_KEY = 'all_units_collection';

    protected string $baseUrl;

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function __construct()
    {
        $this->baseUrl = config('services.sso.unit_url', 'https://staffing.garudapratama.com/api/v1/units');
    }

    /**
     * Fetch all units from the external API with global caching.
     * This focuses on performance by minimizing API calls for the entire dataset.
     */
    public function getAllUnits(): Collection
    {
        $cacheKey = self::CACHE_KEY;

        // 1. Get from cache first
        $cached = Cache::get($cacheKey);
        if ($cached instanceof Collection && $cached->isNotEmpty()) {
            return $cached;
        }

        // 2. Cache is empty or null, fetch from API
        $user = \Illuminate\Support\Facades\Auth::user();
        $accessToken = null;

        if ($user && $user instanceof \App\Models\User) {
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
                    \Illuminate\Support\Facades\Log::error('Failed to refresh token in UnitService::getAllUnits', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);

                    // If token is already expired and refresh failed, don't use the expired token
                    if ($user->isTokenExpired()) {
                        $accessToken = null;

                        return collect([]); // Early return as we know it will fail with 401
                    }
                }
            }
            $accessToken = $accessToken ?? $user->access_token;
        }

        $request = Http::asJson();
        if ($accessToken) {
            $request = $request->withToken($accessToken);
        }

        try {
            $response = $request->get($this->baseUrl, [
                'per_page' => 1000,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $data = $json['data'] ?? (isset($json[0]) ? $json : []);

                $units = collect($data)->map(function ($item) {
                    return new Unit([
                        'id' => $item['id'] ?? $item['ORGANISASI_ID'] ?? null,
                        'code' => $item['code_name'] ?? $item['code'] ?? $item['ORGANISASI_CODE'] ?? $item['ORGANISASI_KODE'] ?? $item['KODE'] ?? null,
                        'name' => $item['name'] ?? $item['ORGANISASI_NAMA'] ?? null,
                        'superior_unit' => $item['superior_unit'] ?? null,
                    ]);
                });

                // 3. Success: Cache for 1 hour (even if empty, because it's a valid API response)
                Cache::put($cacheKey, $units, now()->addHour());

                return $units;
            } else {
                \Illuminate\Support\Facades\Log::warning('API Request unsuccessful in UnitService::getAllUnits', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Exception in UnitService::getAllUnits', [
                'message' => $e->getMessage(),
            ]);
        }

        // 4. Failure: Don't cache or cache very briefly (1 min) to allow retry
        // Returns the cached (stale/empty) value or an empty collection if nothing exists
        return $cached ?? collect([]);
    }

    /**
     * @deprecated Use getAllUnits and paginate manually in the table records() closure.
     */
    public function getUnits(int $page = 1, int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $allUnits = $this->getAllUnits();

        if ($search) {
            $allUnits = $allUnits->filter(function (Unit $unit) use ($search) {
                return str_contains(strtolower($unit->name ?? ''), strtolower($search)) ||
                       str_contains(strtolower($unit->code ?? ''), strtolower($search));
            });
        }

        $items = $allUnits->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $allUnits->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
