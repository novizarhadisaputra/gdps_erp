<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\ProvinceResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\RegencyResource;

class ViewRegency extends ViewRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = RegencyResource::class;

    public function getBreadcrumbs(): array
    {
        $regency = $this->record;
        $province = $regency->province;

        $breadcrumbs = [
            ProvinceResource::getUrl('index') => 'Provinces',
            ProvinceResource::getUrl('edit', ['record' => $province->id]) => $province->name,
            ProvinceResource::getUrl('regencies', ['record' => $province->id]) => 'Regencies',
        ];

        $breadcrumbs[] = $regency->name;

        return $breadcrumbs;
    }
}
