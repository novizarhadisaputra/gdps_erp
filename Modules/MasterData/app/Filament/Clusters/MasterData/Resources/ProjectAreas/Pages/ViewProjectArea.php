<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\ProjectAreaResource;

class ViewProjectArea extends ViewRecord
{
    protected static string $resource = ProjectAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
