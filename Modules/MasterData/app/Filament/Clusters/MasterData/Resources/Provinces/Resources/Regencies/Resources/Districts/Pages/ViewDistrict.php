<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Pages;

use Exception;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\ProvinceResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\RegencyResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\DistrictResource;

class ViewDistrict extends ViewRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = DistrictResource::class;

    public function getBreadcrumbs(): array
    {
        $district = $this->record;
        $regency = $district->regency;
        $province = $regency->province;

        $breadcrumbs = [
            ProvinceResource::getUrl('index') => 'Provinces',
            ProvinceResource::getUrl('edit', ['record' => $province->id]) => $province->name,
            ProvinceResource::getUrl('regencies', ['record' => $province->id]) => 'Regencies',
        ];

        try {
            $regencyUrl = RegencyResource::getUrl('view', [
                'parent' => $province->id,
                'record' => $regency->id,
            ]);
            $breadcrumbs[$regencyUrl] = $regency->name;

            $districtsUrl = RegencyResource::getUrl('districts', [
                'parent' => $province->id,
                'record' => $regency->id,
            ]);
            $breadcrumbs[$districtsUrl] = 'Districts';
        } catch (Exception $e) {
            $breadcrumbs[] = $regency->name;
            $breadcrumbs[] = 'Districts';
        }

        $breadcrumbs[] = $district->name;

        return $breadcrumbs;
    }
}
