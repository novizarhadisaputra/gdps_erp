<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\RegencyResource;

class CreateRegency extends CreateRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = RegencyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['province_id'] = $this->parentRecord->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['province' => $this->parentRecord, 'record' => $this->getRecord()]);
    }
}
