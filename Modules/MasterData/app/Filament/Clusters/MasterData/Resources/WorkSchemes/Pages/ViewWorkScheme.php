<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\WorkSchemeResource;

class ViewWorkScheme extends ViewRecord
{
    protected static string $resource = WorkSchemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
