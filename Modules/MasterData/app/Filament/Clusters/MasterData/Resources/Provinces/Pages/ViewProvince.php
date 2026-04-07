<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\ProvinceResource;

class ViewProvince extends ViewRecord
{
    protected static string $resource = ProvinceResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            ProvinceResource::getUrl('index') => 'Provinces',
            $this->record->name,
        ];
    }
}
