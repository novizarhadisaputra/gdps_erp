<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Bapps\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Project\Filament\Clusters\Project\Resources\Bapps\BappResource;

class ListBapps extends ListRecords
{
    protected static string $resource = BappResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
