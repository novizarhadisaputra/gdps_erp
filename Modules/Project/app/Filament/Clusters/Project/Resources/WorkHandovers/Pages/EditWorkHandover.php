<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Bapps\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Project\Filament\Clusters\Project\Resources\Bapps\BappResource;

class EditBapp extends EditRecord
{
    protected static string $resource = BappResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
