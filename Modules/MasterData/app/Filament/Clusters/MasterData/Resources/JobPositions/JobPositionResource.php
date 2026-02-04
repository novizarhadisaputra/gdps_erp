<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Pages\CreateJobPosition;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Pages\EditJobPosition;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Pages\ListJobPositions;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Schemas\JobPositionForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Tables\JobPositionsTable;

class JobPositionResource extends Resource
{
    protected static ?string $model = JobPosition::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return JobPositionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobPositionsTable::configure($table);
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
            'index' => ListJobPositions::route('/'),
            'create' => CreateJobPosition::route('/create'),
            'edit' => EditJobPosition::route('/{record}/edit'),
        ];
    }
}
