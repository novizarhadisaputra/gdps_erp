<?php

namespace Modules\Project\Filament\Resources\ProjectInformations;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Project\Filament\Resources\ProjectInformations\Pages\CreateProjectInformation;
use Modules\Project\Filament\Resources\ProjectInformations\Pages\EditProjectInformation;
use Modules\Project\Filament\Resources\ProjectInformations\Pages\ListProjectInformations;
use Modules\Project\Filament\Resources\ProjectInformations\Schemas\ProjectInformationForm;
use Modules\Project\Filament\Resources\ProjectInformations\Tables\ProjectInformationTable;
use Modules\Project\Models\ProjectInformation;

class ProjectInformationResource extends Resource
{
    protected static ?string $cluster = \Modules\Project\Filament\Clusters\Project\ProjectCluster::class;

    protected static ?string $model = ProjectInformation::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Project Info';

    protected static ?string $modelLabel = 'Project Information';

    public static function form(Schema $schema): Schema
    {
        return ProjectInformationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectInformationTable::configure($table);
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
            'index' => ListProjectInformations::route('/'),
        ];
    }
}
