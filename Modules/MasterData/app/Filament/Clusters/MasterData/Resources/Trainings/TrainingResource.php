<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Trainings;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Trainings\Pages\ListTrainings;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Trainings\Schemas\TrainingForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Trainings\Tables\TrainingsTable;
use Modules\MasterData\Models\Training;

class TrainingResource extends Resource
{
    protected static ?string $model = Training::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?int $navigationSort = 110;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'Human Resources';

    public static function form(Schema $schema): Schema
    {
        return TrainingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainings::route('/'),
        ];
    }
}
