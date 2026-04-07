<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\DistrictResource;

class CreateDistrict extends CreateRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = DistrictResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['regency_id'] = $this->parentRecord->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', [
            'province' => $this->parentRecord->province,
            'regency' => $this->parentRecord,
            'record' => $this->getRecord()
        ]);
    }
}
