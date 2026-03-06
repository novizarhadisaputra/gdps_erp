<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\DirectCostCategories\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Finance\Filament\Clusters\Finance\Resources\DirectCostCategories\DirectCostCategoryResource;

class ListDirectCostCategories extends ListRecords
{
    protected static string $resource = DirectCostCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
