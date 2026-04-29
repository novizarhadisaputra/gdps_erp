<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmTypes;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmTypes\Pages\CreateJkmType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmTypes\Pages\EditJkmType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmTypes\Pages\ListJkmTypes;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmTypes\Schemas\JkmTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmTypes\Tables\JkmTypesTable;
use Modules\MasterData\Models\JkmType;

class JkmTypeResource extends Resource
{
    protected static ?string $model = JkmType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    public static function form(Schema $schema): Schema
    {
        return JkmTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JkmTypesTable::configure($table);
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
            'index' => ListJkmTypes::route('/'),
            'create' => CreateJkmType::route('/create'),
            'edit' => EditJkmType::route('/{record}/edit'),
        ];
    }
}
