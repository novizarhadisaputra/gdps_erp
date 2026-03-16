<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\Pages\CreateDirectCostCategory;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\Pages\EditDirectCostCategory;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\Pages\ListDirectCostCategories;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\Schemas\DirectCostCategoryForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\Tables\DirectCostCategoriesTable;
use Modules\MasterData\Models\DirectCostCategory;

class DirectCostCategoryResource extends Resource
{
    protected static ?string $model = DirectCostCategory::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DirectCostCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DirectCostCategoriesTable::configure($table);
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
            'index' => ListDirectCostCategories::route('/'),
            'create' => CreateDirectCostCategory::route('/create'),
            'edit' => EditDirectCostCategory::route('/{record}/edit'),
        ];
    }
}
