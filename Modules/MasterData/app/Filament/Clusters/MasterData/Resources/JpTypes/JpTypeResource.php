<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JpTypes;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JpTypes\Pages\CreateJpType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JpTypes\Pages\EditJpType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JpTypes\Pages\ListJpTypes;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JpTypes\Schemas\JpTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JpTypes\Tables\JpTypesTable;
use Modules\MasterData\Models\JpType;

class JpTypeResource extends Resource
{
    protected static ?string $model = JpType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    public static function form(Schema $schema): Schema
    {
        return JpTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JpTypesTable::configure($table);
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
            'index' => ListJpTypes::route('/'),
            'create' => CreateJpType::route('/create'),
            'edit' => EditJpType::route('/{record}/edit'),
        ];
    }
}
