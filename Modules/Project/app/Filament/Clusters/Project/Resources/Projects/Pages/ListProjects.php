<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Support\Icons\Heroicon;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs\Tab;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Schemas\ProjectForm;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('board')
                ->label('Kanban Board')
                ->icon(Heroicon::OutlinedViewColumns)
                ->url(ProjectResource::getUrl('index')),
            CreateAction::make()
                ->schema(fn (Schema $schema) => ProjectForm::configure($schema)),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->icon(Heroicon::OutlinedListBullet),
            'active' => Tab::make()
                ->icon(Heroicon::OutlinedCheckCircle)
                ->modifyQueryUsing(fn ($query) => $query->withoutTrashed()),
            'archived' => Tab::make()
                ->label('Trash / Archived')
                ->icon(Heroicon::OutlinedTrash)
                ->modifyQueryUsing(fn ($query) => $query->onlyTrashed()),
        ];
    }
}
