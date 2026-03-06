<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsBasisTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsBasisTypes\BpjsBasisTypeResource;

class ListBpjsBasisTypes extends ListRecords
{
    protected static string $resource = BpjsBasisTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
