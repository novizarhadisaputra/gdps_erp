<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkPatterns\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkPatterns\WorkPatternResource;

class ViewWorkPattern extends ViewRecord
{
    protected static string $resource = WorkPatternResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
