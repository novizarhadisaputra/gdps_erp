<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\WorkSchemeResource;

class ListWorkSchemes extends ListRecords
{
    protected static string $resource = WorkSchemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
