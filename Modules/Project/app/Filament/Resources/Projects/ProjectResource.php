<?php

namespace Modules\Project\Filament\Resources\Projects;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Project\Filament\Resources\Projects\Pages\ListProjects;
use Modules\Project\Filament\Resources\Projects\Schemas\ProjectForm;
use Modules\Project\Filament\Resources\Projects\Tables\ProjectsTable;
use Modules\Project\Models\Project;

class ProjectResource extends Resource
{
    protected static ?string $cluster = \Modules\Project\Filament\Clusters\Project\ProjectCluster::class;

    protected static ?string $model = Project::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
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
            'index' => ListProjects::route('/'),
        ];
    }
}
