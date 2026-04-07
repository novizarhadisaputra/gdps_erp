<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\DistrictResource;

class ListDistricts extends ListRecords
{
    use InteractsWithParentRecord;

    protected static string $resource = DistrictResource::class;
}
