<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Project\Filament\Clusters\Project\ProjectCluster;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages\EditProject;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages\ListProjects;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages\ManageDailyReports;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages\ManageProjectChangeRequests;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages\ManageProjectComments;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages\ManageProjectInformations;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages\ManageProjectMembers;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages\ManageProjectTasks;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages\ManageWorkCompletionReports;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages\ProjectBoard;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages\ViewProject;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Schemas\ProjectForm;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Schemas\ProjectInfolist;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Tables\ProjectsTable;
use Modules\Project\Models\Project;

class ProjectResource extends Resource
{
    protected static ?string $cluster = ProjectCluster::class;

    protected static ?string $model = Project::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?int $navigationSort = 1;

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Start;
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewProject::class,
            EditProject::class,
            ManageProjectInformations::class,
            ManageProjectMembers::class,
            ManageProjectTasks::class,
            ManageDailyReports::class,
            ManageProjectChangeRequests::class,
            ManageProjectComments::class,
            ManageWorkCompletionReports::class,
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProjectInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ProjectBoard::route('/'),
            'list' => ListProjects::route('/list'),
            'view' => ViewProject::route('/{record}'),
            'edit' => EditProject::route('/{record}/edit'),
            'project-informations' => ManageProjectInformations::route('/{record}/project-informations'),
            'project-members' => ManageProjectMembers::route('/{record}/members'),
            'project-tasks' => ManageProjectTasks::route('/{record}/tasks'),
            'project-daily-reports' => ManageDailyReports::route('/{record}/daily-reports'),
            'project-comments' => ManageProjectComments::route('/{record}/discussions'),
            'project-change-requests' => ManageProjectChangeRequests::route('/{record}/change-requests'),
            'work-completion-reports' => ManageWorkCompletionReports::route('/{record}/work-completion-reports'),
        ];
    }
}
