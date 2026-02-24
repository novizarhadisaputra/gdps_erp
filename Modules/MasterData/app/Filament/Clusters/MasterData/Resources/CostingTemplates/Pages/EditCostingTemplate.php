<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates\CostingTemplateResource;

class EditCostingTemplate extends EditRecord
{
    protected static string $resource = CostingTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
