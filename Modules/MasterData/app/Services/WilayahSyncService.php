<?php

namespace Modules\MasterData\Services;

use Illuminate\Support\Facades\Http;
use Modules\MasterData\Models\Province;
use Modules\MasterData\Models\Regency;
use Modules\MasterData\Models\District;
use Modules\MasterData\Models\Village;

class WilayahSyncService
{
    protected string $baseUrl = 'https://wilayah.id/api';

    /**
     * Sync all provinces from the API.
     */
    public function syncProvinces(): void
    {
        $response = Http::get("{$this->baseUrl}/provinces.json");

        if ($response->successful()) {
            $provinces = $response->json('data');

            foreach ($provinces as $province) {
                Province::updateOrCreate(
                    ['code' => $province['code']],
                    ['name' => $province['name']]
                );
            }
        }
    }

    /**
     * Sync all regencies/cities for a specific province.
     */
    public function syncRegencies(Province $province): void
    {
        $response = Http::get("{$this->baseUrl}/regencies/{$province->code}.json");

        if ($response->successful()) {
            $regencies = $response->json('data');

            foreach ($regencies as $regency) {
                Regency::updateOrCreate(
                    ['code' => $regency['code']],
                    [
                        'name' => $regency['name'],
                        'province_id' => $province->id,
                    ]
                );
            }
        }
    }

    /**
     * Sync all regencies/cities for all known provinces.
     */
    public function syncAllRegencies(): void
    {
        $provinces = Province::all();

        foreach ($provinces as $province) {
            $this->syncRegencies($province);
        }
    }

    /**
     * Sync districts for a specific regency.
     */
    public function syncDistricts(Regency $regency): void
    {
        $response = Http::get("{$this->baseUrl}/districts/{$regency->code}.json");

        if ($response->successful()) {
            $districts = $response->json('data');

            foreach ($districts as $district) {
                District::updateOrCreate(
                    ['code' => $district['code']],
                    [
                        'name' => $district['name'],
                        'regency_id' => $regency->id,
                    ]
                );
            }
        }
    }

    /**
     * Sync villages for a specific district.
     */
    public function syncVillages(District $district): void
    {
        $response = Http::get("{$this->baseUrl}/villages/{$district->code}.json");

        if ($response->successful()) {
            $villages = $response->json('data');

            foreach ($villages as $village) {
                Village::updateOrCreate(
                    ['code' => $village['code']],
                    [
                        'name' => $village['name'],
                        'district_id' => $district->id,
                    ]
                );
            }
        }
    }
}
