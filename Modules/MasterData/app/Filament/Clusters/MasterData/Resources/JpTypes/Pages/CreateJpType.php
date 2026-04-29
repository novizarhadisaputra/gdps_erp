<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JpTypes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JpTypes\JpTypeResource;

class CreateJpType extends CreateRecord
{
    protected static string $resource = JpTypeResource::class;
}
