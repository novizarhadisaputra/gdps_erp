<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkPatterns;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkPatterns\Pages\CreateWorkPattern;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkPatterns\Pages\EditWorkPattern;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkPatterns\Pages\ListWorkPatterns;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkPatterns\Pages\ViewWorkPattern;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkPatterns\Schemas\WorkPatternForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkPatterns\Tables\WorkPatternsTable;
use Modules\MasterData\Models\WorkPattern;

class WorkPatternResource extends Resource
{
    protected static ?string $model = WorkPattern::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?int $navigationSort = 110;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|\UnitEnum|null $navigationGroup = 'HR & Organization';

    public static function form(Schema $schema): Schema
    {
        return WorkPatternForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkPatternsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkPatterns::route('/'),
            'create' => CreateWorkPattern::route('/create'),
            'view' => ViewWorkPattern::route('/{record}'),
            'edit' => EditWorkPattern::route('/{record}/edit'),
        ];
    }
}
