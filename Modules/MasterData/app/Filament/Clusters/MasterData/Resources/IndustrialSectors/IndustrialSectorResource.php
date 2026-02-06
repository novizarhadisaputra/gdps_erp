<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Pages\ListIndustrialSectors;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Schemas\IndustrialSectorForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Tables\IndustrialSectorsTable;
use Modules\MasterData\Models\IndustrialSector;

class IndustrialSectorResource extends Resource
{
    protected static ?string $model = IndustrialSector::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales Master';

    protected static ?int $navigationSort = 3;

    protected static ?string $cluster = MasterDataCluster::class;

    public static function form(Schema $schema): Schema
    {
        return IndustrialSectorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IndustrialSectorsTable::configure($table);
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
            'index' => ListIndustrialSectors::route('/'),
        ];
    }
}
