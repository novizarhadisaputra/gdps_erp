<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests\ProjectChangeRequestResource;

class ListProjectChangeRequests extends ListRecords
{
    protected static string $resource = ProjectChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label('New Change Request')
                ->icon('heroicon-o-plus'),
        ];
    }
}
