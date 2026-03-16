<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsBasisTypes;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsBasisTypes\Pages\CreateBpjsBasisType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsBasisTypes\Pages\EditBpjsBasisType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsBasisTypes\Pages\ListBpjsBasisTypes;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsBasisTypes\Schemas\BpjsBasisTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsBasisTypes\Tables\BpjsBasisTypesTable;
use Modules\MasterData\Models\BpjsBasisType;

class BpjsBasisTypeResource extends Resource
{
    protected static ?string $model = BpjsBasisType::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BpjsBasisTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BpjsBasisTypesTable::configure($table);
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
            'index' => ListBpjsBasisTypes::route('/'),
            'create' => CreateBpjsBasisType::route('/create'),
            'edit' => EditBpjsBasisType::route('/{record}/edit'),
        ];
    }
}
