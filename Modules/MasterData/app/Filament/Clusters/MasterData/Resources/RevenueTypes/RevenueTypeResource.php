<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes\Pages\CreateRevenueType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes\Pages\EditRevenueType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes\Pages\ListRevenueTypes;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes\Schemas\RevenueTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes\Tables\RevenueTypesTable;
use Modules\MasterData\Models\RevenueType;

class RevenueTypeResource extends Resource
{
    protected static ?string $model = RevenueType::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Project Setup';

    protected static ?int $navigationSort = 42;

    protected static ?string $cluster = MasterDataCluster::class;

    public static function form(Schema $schema): Schema
    {
        return RevenueTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RevenueTypesTable::configure($table);
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
            'index' => ListRevenueTypes::route('/'),
            'create' => CreateRevenueType::route('/create'),
            'edit' => EditRevenueType::route('/{record}/edit'),
        ];
    }
}
