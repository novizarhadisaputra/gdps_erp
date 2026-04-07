<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\ProvinceResource;

class EditProvince extends EditRecord
{
    protected static string $resource = ProvinceResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            ProvinceResource::getUrl('index') => 'Provinces',
            ProvinceResource::getUrl('view', ['record' => $this->record->id]) => $this->record->name,
            'Edit',
        ];
    }
}
