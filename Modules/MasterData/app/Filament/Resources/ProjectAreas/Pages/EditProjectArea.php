<?php

namespace Modules\MasterData\Filament\Resources\ProjectAreas\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Resources\ProjectAreas\ProjectAreaResource;

class EditProjectArea extends EditRecord
{
    protected static string $resource = ProjectAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
