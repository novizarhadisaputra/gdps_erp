<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BufferCostTypes;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BufferCostTypes\Pages\CreateBufferCostType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BufferCostTypes\Pages\EditBufferCostType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BufferCostTypes\Pages\ListBufferCostTypes;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BufferCostTypes\Schemas\BufferCostTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BufferCostTypes\Tables\BufferCostTypesTable;
use Modules\MasterData\Models\BufferCostType;

class BufferCostTypeResource extends Resource
{
    protected static ?string $model = BufferCostType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BufferCostTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BufferCostTypesTable::configure($table);
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
            'index' => ListBufferCostTypes::route('/'),
            'create' => CreateBufferCostType::route('/create'),
            'edit' => EditBufferCostType::route('/{record}/edit'),
        ];
    }
}
