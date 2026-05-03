<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectInformations\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectInformations\ProjectInformationResource;

class EditProjectInformation extends EditRecord
{
    protected static string $resource = ProjectInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
