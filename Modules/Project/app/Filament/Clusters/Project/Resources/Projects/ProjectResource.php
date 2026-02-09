<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Project\Filament\Clusters\Project\ProjectCluster;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages\EditProject;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages\ListProjects;
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

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 10;

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
        ];
    }
}
