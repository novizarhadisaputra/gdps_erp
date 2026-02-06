<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ServiceLines\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ServiceLines\ServiceLineResource;

class EditServiceLine extends EditRecord
{
    protected static string $resource = ServiceLineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
