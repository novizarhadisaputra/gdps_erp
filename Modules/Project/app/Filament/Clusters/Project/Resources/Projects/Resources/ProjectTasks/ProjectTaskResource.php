<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectTasks;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectTasks\Pages\ManageProjectTaskComments;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectTasks\Schemas\ProjectTaskForm;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectTasks\Tables\ProjectTasksTable;
use Modules\Project\Models\ProjectTask;

class ProjectTaskResource extends Resource
{
    protected static ?string $model = ProjectTask::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = ProjectResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedListBullet;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ProjectTaskForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectTasksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'discussions' => ManageProjectTaskComments::route('/{record}/discussions'),
        ];
    }
}
