<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtTypes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtTypes\JhtTypeResource;

class CreateJhtType extends CreateRecord
{
    protected static string $resource = JhtTypeResource::class;
}
