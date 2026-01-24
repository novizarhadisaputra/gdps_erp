<?php

namespace Modules\MasterData\Filament\Resources\ItemCategories\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Resources\ItemCategories\ItemCategoryResource;

class ListItemCategories extends ListRecords
{
    protected static string $resource = ItemCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
