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
     * Fetch all units from the external API and sync with local database.
     */
    public function syncFromApi(): Collection
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (! ($user instanceof \App\Models\User)) {
            return collect([]);
        }

        // 1. Proactive Refresh if needed
        if ($user->needsTokenRefresh()) {
            if (! $user->refreshSsoToken()) {
                \Illuminate\Support\Facades\Log::warning('UnitService::syncFromApi: Proactive refresh failed.');

                return collect([]);
            }
        }

        $accessToken = $user->access_token;

        if (! $accessToken) {
            return collect([]);
        }

        try {
            // 2. Initial Request
            $response = Http::asJson()
                ->withHeader('Accept', 'application/json')
                ->withToken($accessToken)
                ->get($this->baseUrl, ['per_page' => 1000]);

            $json = $response->json();

            // 3. Handle Unauthorized (401) or explicit "jwt expired"
            if ($response->status() === 401 || ($json['message'] ?? '') === 'jwt expired') {
                \Illuminate\Support\Facades\Log::info('UnitService::syncFromApi: Token expired mid-request, attempting final refresh.');

                if ($user->refreshSsoToken()) {
                    // Retry with new token
                    $response = Http::asJson()
                        ->withHeader('Accept', 'application/json')
                        ->withToken($user->access_token)
                        ->get($this->baseUrl, ['per_page' => 1000]);

                    $json = $response->json();
                } else {
                    \Illuminate\Support\Facades\Log::warning('UnitService::syncFromApi: Final refresh failed after 401/expired JWT. User session might be expired.');

                    return collect([]);
                }
            }

            if ($response->successful()) {
                $data = $json['data'] ?? (isset($json[0]) ? $json : []);

                \Illuminate\Support\Facades\Log::info('UnitService::syncFromApi fetched '.count($data).' units.');

                $syncedUnits = collect();

                foreach ($data as $item) {
                    $externalId = $item['id'] ?? $item['ORGANISASI_ID'] ?? null;

                    if (! $externalId) {
                        continue;
                    }

                    $unit = Unit::updateOrCreate(
                        ['external_id' => (string) $externalId],
                        [
                            'code' => $item['code_name'] ?? $item['code'] ?? $item['ORGANISASI_CODE'] ?? $item['ORGANISASI_KODE'] ?? $item['KODE'] ?? null,
                            'name' => $item['name'] ?? $item['ORGANISASI_NAMA'] ?? null,
                            'superior_unit' => $item['superior_unit'] ?? null,
                        ]
                    );

                    $syncedUnits->push($unit);
                }

                self::clearCache();

                return $syncedUnits;
            } else {
                \Illuminate\Support\Facades\Log::error('UnitService::syncFromApi request failed after potential retry', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Exception in UnitService::syncFromApi', [
                'message' => $e->getMessage(),
            ]);
        }

        return collect([]);
    }

    /**
     * Get all units from the local database.
     */
    public function getAllUnits(): Collection
    {
        $cacheKey = self::CACHE_KEY;

        return Cache::remember($cacheKey, now()->addHour(), function () {
            return Unit::all();
        });
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
