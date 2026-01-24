<?php

namespace Modules\MasterData\Filament\Resources\ProjectTypes;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Resources\ProjectTypes\Pages\CreateProjectType;
use Modules\MasterData\Filament\Resources\ProjectTypes\Pages\EditProjectType;
use Modules\MasterData\Filament\Resources\ProjectTypes\Pages\ListProjectTypes;
use Modules\MasterData\Filament\Resources\ProjectTypes\Schemas\ProjectTypeForm;
use Modules\MasterData\Filament\Resources\ProjectTypes\Tables\ProjectTypesTable;
use Modules\MasterData\Models\ProjectType;

class ProjectTypeResource extends Resource
{
    protected static ?string $cluster = \Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster::class;

    protected static ?string $model = ProjectType::class;

    protected static ?int $navigationSort = 7;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ProjectTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectTypesTable::configure($table);
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
            'index' => ListProjectTypes::route('/'),
        ];
    }
}
