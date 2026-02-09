<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ManpowerTemplates\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ManpowerTemplateResource;

class ListManpowerTemplates extends ListRecords
{
    protected static string $resource = ManpowerTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
