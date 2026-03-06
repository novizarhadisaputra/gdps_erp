<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BufferCostTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BufferCostTypes\BufferCostTypeResource;

class ListBufferCostTypes extends ListRecords
{
    protected static string $resource = BufferCostTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
