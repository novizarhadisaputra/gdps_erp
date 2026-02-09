<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ManpowerTemplates\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ManpowerTemplateResource;

class ViewManpowerTemplate extends ViewRecord
{
    protected static string $resource = ManpowerTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
