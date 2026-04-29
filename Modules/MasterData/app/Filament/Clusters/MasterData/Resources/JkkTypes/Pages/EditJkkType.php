<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkTypes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkTypes\JkkTypeResource;

class EditJkkType extends EditRecord
{
    protected static string $resource = JkkTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
