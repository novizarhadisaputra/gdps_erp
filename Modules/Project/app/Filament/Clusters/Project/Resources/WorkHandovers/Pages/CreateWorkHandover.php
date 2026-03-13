<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Bapps\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Project\Filament\Clusters\Project\Resources\Bapps\BappResource;

class CreateBapp extends CreateRecord
{
    protected static string $resource = BappResource::class;
}
