<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\VillageResource;

class ListVillages extends ListRecords
{
    use InteractsWithParentRecord;

    protected static string $resource = VillageResource::class;
}
