<?php

namespace Modules\Project\Filament\Resources\Projects\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Project\Filament\Resources\Projects\ProjectResource;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;
}
