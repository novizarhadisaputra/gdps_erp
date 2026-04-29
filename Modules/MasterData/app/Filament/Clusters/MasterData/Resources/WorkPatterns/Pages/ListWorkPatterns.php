<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkPatterns\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkPatterns\WorkPatternResource;

class ListWorkPatterns extends ListRecords
{
    protected static string $resource = WorkPatternResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
