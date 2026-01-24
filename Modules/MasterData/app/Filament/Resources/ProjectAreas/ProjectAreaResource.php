<?php

namespace Modules\MasterData\Filament\Resources\ProjectAreas;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Resources\ProjectAreas\Pages\CreateProjectArea;
use Modules\MasterData\Filament\Resources\ProjectAreas\Pages\EditProjectArea;
use Modules\MasterData\Filament\Resources\ProjectAreas\Pages\ListProjectAreas;
use Modules\MasterData\Filament\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Filament\Resources\ProjectAreas\Tables\ProjectAreasTable;
use Modules\MasterData\Models\ProjectArea;

class ProjectAreaResource extends Resource
{
    protected static ?string $cluster = \Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster::class;

    protected static ?string $model = ProjectArea::class;

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ProjectAreaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectAreasTable::configure($table);
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
            'index' => ListProjectAreas::route('/'),
        ];
    }
}
