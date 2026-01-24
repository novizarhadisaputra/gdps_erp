<?php

namespace Modules\MasterData\Filament\Resources\ItemCategories\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Resources\ItemCategories\ItemCategoryResource;

class EditItemCategory extends EditRecord
{
    protected static string $resource = ItemCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
