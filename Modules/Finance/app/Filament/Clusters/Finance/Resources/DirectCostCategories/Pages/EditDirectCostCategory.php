<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\DirectCostCategories\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\DirectCostCategories\DirectCostCategoryResource;

class EditDirectCostCategory extends EditRecord
{
    protected static string $resource = DirectCostCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
