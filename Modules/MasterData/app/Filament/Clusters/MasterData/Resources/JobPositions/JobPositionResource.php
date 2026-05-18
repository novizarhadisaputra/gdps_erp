<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Pages\CreateJobPosition;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Pages\EditJobPosition;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Pages\ListJobPositions;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Pages\ViewJobPosition;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Schemas\JobPositionForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Tables\JobPositionsTable;
use Modules\MasterData\Models\JobPosition;

class JobPositionResource extends Resource
{
    protected static ?string $model = JobPosition::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?int $navigationSort = 100;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|\UnitEnum|null $navigationGroup = 'HR & Organization';

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
            'view' => ViewJobPosition::route('/{record}'),
            'edit' => EditJobPosition::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Job Position');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Job Positions');
    }

    public static function getNavigationLabel(): string
    {
        return __('Job Positions');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('HR & Organization');
    }
}
