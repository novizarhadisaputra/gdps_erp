<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BufferCostTypes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BufferCostTypes\BufferCostTypeResource;

class EditBufferCostType extends EditRecord
{
    protected static string $resource = BufferCostTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
