<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\Pages\ListRevenueSegments;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\Schemas\RevenueSegmentForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\Tables\RevenueSegmentsTable;
use Modules\MasterData\Models\RevenueSegment;

class RevenueSegmentResource extends Resource
{
    protected static ?string $model = RevenueSegment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales Master';

    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = MasterDataCluster::class;

    public static function form(Schema $schema): Schema
    {
        return RevenueSegmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RevenueSegmentsTable::configure($table);
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
            'index' => ListRevenueSegments::route('/'),
        ];
    }
}
