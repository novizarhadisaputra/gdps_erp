<?php

namespace Modules\Project\Filament\Resources\ProjectInformations\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Project\Filament\Resources\ProjectInformations\ProjectInformationResource;

class EditProjectInformation extends EditRecord
{
    protected static string $resource = ProjectInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
