<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkEquipments;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkEquipments\Pages\ListWorkEquipments;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkEquipments\Schemas\WorkEquipmentForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkEquipments\Tables\WorkEquipmentsTable;
use Modules\MasterData\Models\WorkEquipment;

class WorkEquipmentResource extends Resource
{
    protected static ?string $model = WorkEquipment::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?int $navigationSort = 120;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|\UnitEnum|null $navigationGroup = 'Resources';

    public static function form(Schema $schema): Schema
    {
        return WorkEquipmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkEquipmentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkEquipments::route('/'),
        ];
    }
}
