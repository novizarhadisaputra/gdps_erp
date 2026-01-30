<?php

namespace Modules\MasterData\Filament\Resources\CostingTemplates\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Resources\CostingTemplateResource;

class ListCostingTemplates extends ListRecords
{
    protected static string $resource = CostingTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
