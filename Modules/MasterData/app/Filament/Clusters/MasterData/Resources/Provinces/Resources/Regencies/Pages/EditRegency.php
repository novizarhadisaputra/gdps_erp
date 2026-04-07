<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Pages;

use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\RegencyResource;

class EditRegency extends EditRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = RegencyResource::class;
}
