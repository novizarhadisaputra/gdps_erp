<?php

namespace Modules\MasterData\Filament\Resources\ProjectTypes;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Resources\ProjectTypes\Pages\ListProjectTypes;
use Modules\MasterData\Filament\Resources\ProjectTypes\Schemas\ProjectTypeForm;
use Modules\MasterData\Filament\Resources\ProjectTypes\Tables\ProjectTypesTable;
use Modules\MasterData\Models\ProjectType;

class ProjectTypeResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = ProjectType::class;

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static string|\UnitEnum|null $navigationGroup = 'Project Structure';

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
