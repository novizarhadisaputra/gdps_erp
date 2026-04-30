<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\MinimumWages\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\MinimumWages\MinimumWageResource;

class ViewMinimumWage extends ViewRecord
{
    protected static string $resource = MinimumWageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
