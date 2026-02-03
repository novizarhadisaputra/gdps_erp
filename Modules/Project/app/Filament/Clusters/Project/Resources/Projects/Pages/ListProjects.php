<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('board')
                ->label('Kanban Board')
                ->icon('heroicon-o-view-columns')
                ->url(ProjectResource::getUrl('index')),
            CreateAction::make(),
        ];
    }
}
