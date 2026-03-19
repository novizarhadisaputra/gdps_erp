<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\WorkCompletionReportResource;

class ManageWorkCompletionReportComments extends ManageRelatedRecords
{
    protected static string $resource = ProjectResource::class;

    protected static ?string $relatedResource = WorkCompletionReportResource::class;

    protected static string $relationship = 'comments';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Discussions';

    protected string $view = 'Modules.Project.resources.views.filament.clusters.project.resources.projects.pages.manage-project-comments';

    public function getTitle(): string
    {
        return 'Work Completion Report Discussions';
    }
}
