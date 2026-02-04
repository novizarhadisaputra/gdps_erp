<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RemunerationComponents\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RemunerationComponents\RemunerationComponentResource;

class EditRemunerationComponent extends EditRecord
{
    protected static string $resource = RemunerationComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
