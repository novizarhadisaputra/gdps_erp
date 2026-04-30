<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Project\Filament\Clusters\Project\ProjectCluster;
use Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests\Pages\CreateProjectChangeRequest;
use Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests\Pages\EditProjectChangeRequest;
use Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests\Pages\ListProjectChangeRequests;
use Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests\Pages\ViewProjectChangeRequest;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Schemas\ProjectChangeRequestForm;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Tables\ProjectChangeRequestsTable;
use Modules\Project\Models\ProjectChangeRequest;

class ProjectChangeRequestResource extends Resource
{
    protected static ?string $model = ProjectChangeRequest::class;

    protected static ?string $cluster = ProjectCluster::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ProjectChangeRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectChangeRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjectChangeRequests::route('/'),
            'create' => CreateProjectChangeRequest::route('/create'),
            'view' => ViewProjectChangeRequest::route('/{record}'),
            'edit' => EditProjectChangeRequest::route('/{record}/edit'),
        ];
    }
}
