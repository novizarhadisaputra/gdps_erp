<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Pages\ListProjectAreas;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Tables\ProjectAreasTable;
use Modules\MasterData\Models\ProjectArea;

class ProjectAreaResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = ProjectArea::class;

    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|\UnitEnum|null $navigationGroup = 'Projects';

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
