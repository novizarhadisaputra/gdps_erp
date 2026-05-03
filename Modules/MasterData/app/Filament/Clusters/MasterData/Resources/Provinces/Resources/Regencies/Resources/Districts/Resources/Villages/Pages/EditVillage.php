<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\Pages;

use Exception;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\ProvinceResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\RegencyResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\DistrictResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\VillageResource;

class EditVillage extends EditRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = VillageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        $village = $this->record;
        $district = $village->district;
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

        try {
            $districtUrl = DistrictResource::getUrl('view', [
                'parent' => $regency->id,
                'record' => $district->id,
            ]);
            $breadcrumbs[$districtUrl] = $district->name;

            $villagesUrl = DistrictResource::getUrl('villages', [
                'parent' => $regency->id,
                'record' => $district->id,
            ]);
            $breadcrumbs[$villagesUrl] = 'Villages';
        } catch (Exception $e) {
            $breadcrumbs[] = $district->name;
            $breadcrumbs[] = 'Villages';
        }

        try {
            $villageUrl = VillageResource::getUrl('view', [
                'parent' => $district->id,
                'record' => $village->id,
            ]);
            $breadcrumbs[$villageUrl] = $village->name;
        } catch (Exception $e) {
            $breadcrumbs[] = $village->name;
        }

        $breadcrumbs[] = 'Edit';

        return $breadcrumbs;
    }
}
