<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmTypes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmTypes\JkmTypeResource;

class EditJkmType extends EditRecord
{
    protected static string $resource = JkmTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
