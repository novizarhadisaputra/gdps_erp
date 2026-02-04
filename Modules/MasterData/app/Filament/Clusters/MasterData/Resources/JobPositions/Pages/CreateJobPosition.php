<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\JobPositionResource;

class CreateJobPosition extends CreateRecord
{
    protected static string $resource = JobPositionResource::class;
}
