<?php

namespace Modules\MasterData\Filament\Resources\ItemCategories\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Resources\ItemCategories\ItemCategoryResource;

class CreateItemCategory extends CreateRecord
{
    protected static string $resource = ItemCategoryResource::class;
}
