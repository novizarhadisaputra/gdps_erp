<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Pages\CreateProjectChangeRequest;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Pages\EditProjectChangeRequest;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Pages\ListProjectChangeRequests;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Pages\ViewProjectChangeRequest;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Schemas\ProjectChangeRequestForm;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Tables\ProjectChangeRequestsTable;
use Modules\Project\Models\ProjectChangeRequest;

class ProjectChangeRequestResource extends Resource
{
    protected static ?string $model = ProjectChangeRequest::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = ProjectResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

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
