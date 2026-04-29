<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkTypes;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkTypes\Pages\CreateJkkType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkTypes\Pages\EditJkkType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkTypes\Pages\ListJkkTypes;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkTypes\Schemas\JkkTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkTypes\Tables\JkkTypesTable;
use Modules\MasterData\Models\JkkType;

class JkkTypeResource extends Resource
{
    protected static ?string $model = JkkType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    public static function form(Schema $schema): Schema
    {
        return JkkTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JkkTypesTable::configure($table);
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
            'index' => ListJkkTypes::route('/'),
            'create' => CreateJkkType::route('/create'),
            'edit' => EditJkkType::route('/{record}/edit'),
        ];
    }
}
