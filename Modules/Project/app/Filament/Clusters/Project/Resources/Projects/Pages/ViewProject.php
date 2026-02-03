<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
