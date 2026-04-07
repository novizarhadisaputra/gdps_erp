<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Pages;

use Exception;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\ProvinceResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\RegencyResource;

class EditRegency extends EditRecord
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

        try {
            $regencyUrl = RegencyResource::getUrl('view', [
                'parent' => $province->id,
                'record' => $regency->id,
            ]);
            $breadcrumbs[$regencyUrl] = $regency->name;
        } catch (Exception $e) {
            $breadcrumbs[] = $regency->name;
        }

        $breadcrumbs[] = 'Edit';

        return $breadcrumbs;
    }
}
