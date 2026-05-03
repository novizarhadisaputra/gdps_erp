<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\DirectCostCategoryResource;

class EditDirectCostCategory extends EditRecord
{
    protected static string $resource = DirectCostCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
