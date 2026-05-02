<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectInformations;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectInformations\Pages\EditProjectInformation;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectInformations\Pages\ListProjectInformations;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectInformations\Pages\ViewProjectInformation;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectInformations\Schemas\ProjectInformationForm;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectInformations\Tables\ProjectInformationTable;
use Modules\Project\Models\ProjectInformation;

class ProjectInformationResource extends Resource
{
    protected static ?string $model = ProjectInformation::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = ProjectResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedInformationCircle;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ProjectInformationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectInformationTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjectInformations::route('/'),
            'edit' => EditProjectInformation::route('/{record}/edit'),
            'view' => ViewProjectInformation::route('/{record}'),
        ];
    }
}
