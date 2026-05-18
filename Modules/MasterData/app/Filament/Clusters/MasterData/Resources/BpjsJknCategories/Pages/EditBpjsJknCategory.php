<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJknCategories\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJknCategories\BpjsJknCategoryResource;

class EditBpjsJknCategory extends EditRecord
{
    protected static string $resource = BpjsJknCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
