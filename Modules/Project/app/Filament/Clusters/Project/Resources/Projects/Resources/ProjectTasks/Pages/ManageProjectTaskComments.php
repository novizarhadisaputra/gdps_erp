<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectTasks\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectTasks\ProjectTaskResource;

class ManageProjectTaskComments extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ProjectTaskResource::class;

    protected static ?string $title = 'Task Discussions';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected string $view = 'project::filament.clusters.project.resources.project-tasks.pages.manage-project-task-comments';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
