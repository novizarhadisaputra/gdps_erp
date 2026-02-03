<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
