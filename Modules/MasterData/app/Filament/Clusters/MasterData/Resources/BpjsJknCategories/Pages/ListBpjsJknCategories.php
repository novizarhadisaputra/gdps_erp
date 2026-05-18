<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJknCategories\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJknCategories\BpjsJknCategoryResource;

class ListBpjsJknCategories extends ListRecords
{
    protected static string $resource = BpjsJknCategoryResource::class;

    public function getSubheading(): ?string
    {
        return 'Manage standard BPJS JKN / Health participation categories.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
