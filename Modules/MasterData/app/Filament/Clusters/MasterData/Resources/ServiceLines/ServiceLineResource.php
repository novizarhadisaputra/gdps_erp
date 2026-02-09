<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ServiceLines;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ServiceLines\Pages\ListServiceLines;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ServiceLines\Schemas\ServiceLineForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ServiceLines\Tables\ServiceLinesTable;
use Modules\MasterData\Models\ServiceLine;

class ServiceLineResource extends Resource
{
    protected static ?string $model = ServiceLine::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-queue-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales Master';

    protected static ?int $navigationSort = 21;

    protected static ?string $cluster = MasterDataCluster::class;

    public static function form(Schema $schema): Schema
    {
        return ServiceLineForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceLinesTable::configure($table);
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
            'index' => ListServiceLines::route('/'),
        ];
    }
}
