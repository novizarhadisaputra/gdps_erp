<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\Pages;

use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\VillageResource;

class EditVillage extends EditRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = VillageResource::class;
}
