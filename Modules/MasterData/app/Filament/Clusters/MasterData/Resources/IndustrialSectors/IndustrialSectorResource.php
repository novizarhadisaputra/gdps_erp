<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Pages\CreateIndustrialSector;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Pages\EditIndustrialSector;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Pages\ListIndustrialSectors;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Pages\ViewIndustrialSector;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Schemas\IndustrialSectorForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Tables\IndustrialSectorsTable;
use Modules\MasterData\Models\IndustrialSector;

class IndustrialSectorResource extends Resource
{
    protected static ?string $model = IndustrialSector::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales Master';

    protected static ?int $navigationSort = 22;

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
            'create' => CreateIndustrialSector::route('/create'),
            'view' => ViewIndustrialSector::route('/{record}'),
            'edit' => EditIndustrialSector::route('/{record}/edit'),
        ];
    }
}
