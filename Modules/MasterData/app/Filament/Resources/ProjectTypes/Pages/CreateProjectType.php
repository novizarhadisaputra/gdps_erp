<?php

namespace Modules\MasterData\Filament\Resources\ProjectTypes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Resources\ProjectTypes\ProjectTypeResource;

class CreateProjectType extends CreateRecord
{
    protected static string $resource = ProjectTypeResource::class;
}
