<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates\CostingTemplateResource;

class ListCostingTemplates extends ListRecords
{
    protected static string $resource = CostingTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            Actions\CreateAction::make(),
        ];
    }
}
