<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectMembers;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectMembers\Schemas\ProjectMemberForm;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectMembers\Tables\ProjectMembersTable;
use Modules\Project\Models\ProjectMember;

class ProjectMemberResource extends Resource
{
    protected static ?string $model = ProjectMember::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = ProjectResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ProjectMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectMembersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            // Standard resource pages are not needed if we use ManageRelatedRecords
        ];
    }
}
