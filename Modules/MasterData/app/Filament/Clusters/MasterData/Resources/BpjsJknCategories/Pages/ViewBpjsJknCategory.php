<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJknCategories\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJknCategories\BpjsJknCategoryResource;

class ViewBpjsJknCategory extends ViewRecord
{
    protected static string $resource = BpjsJknCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
