<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JpTypes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JpTypes\JpTypeResource;

class EditJpType extends EditRecord
{
    protected static string $resource = JpTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
