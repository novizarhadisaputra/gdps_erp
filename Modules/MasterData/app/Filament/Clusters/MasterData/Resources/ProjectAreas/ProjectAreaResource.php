<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Pages\CreateProjectArea;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Pages\EditProjectArea;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Pages\ListProjectAreas;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Pages\ViewProjectArea;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Tables\ProjectAreasTable;
use Modules\MasterData\Models\ProjectArea;

class ProjectAreaResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = ProjectArea::class;

    protected static ?int $navigationSort = 41;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedMapPin;

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
            'create' => CreateProjectArea::route('/create'),
            'view' => ViewProjectArea::route('/{record}'),
            'edit' => EditProjectArea::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Project Area');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Project Areas');
    }

    public static function getNavigationLabel(): string
    {
        return __('Project Areas');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Projects');
    }
}
