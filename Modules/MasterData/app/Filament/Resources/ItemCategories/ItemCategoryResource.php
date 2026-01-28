<?php

namespace Modules\MasterData\Filament\Resources\ItemCategories;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Resources\ItemCategories\Pages\ListItemCategories;
use Modules\MasterData\Filament\Resources\ItemCategories\Schemas\ItemCategoryForm;
use Modules\MasterData\Filament\Resources\ItemCategories\Tables\ItemCategoriesTable;
use Modules\MasterData\Models\ItemCategory;

class ItemCategoryResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = ItemCategory::class;

    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|\UnitEnum|null $navigationGroup = 'Product & Inventory';

    public static function form(Schema $schema): Schema
    {
        return ItemCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItemCategories::route('/'),
        ];
    }
}
