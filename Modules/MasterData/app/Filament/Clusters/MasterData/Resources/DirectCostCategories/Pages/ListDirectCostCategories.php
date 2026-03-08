<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\DirectCostCategoryResource;

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
