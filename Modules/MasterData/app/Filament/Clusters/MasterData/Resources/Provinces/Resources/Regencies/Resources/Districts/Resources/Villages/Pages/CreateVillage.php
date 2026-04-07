<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\VillageResource;

class CreateVillage extends CreateRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = VillageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['district_id'] = $this->parentRecord->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', [
            'province' => $this->parentRecord->regency->province,
            'regency' => $this->parentRecord->regency,
            'district' => $this->parentRecord,
            'record' => $this->getRecord()
        ]);
    }
}
