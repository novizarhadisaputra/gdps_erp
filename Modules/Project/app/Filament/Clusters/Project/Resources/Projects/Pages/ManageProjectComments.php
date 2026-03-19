<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;

class ManageProjectComments extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'Discussions';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected string $view = 'project::filament.clusters.project.resources.projects.pages.manage-project-comments';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
