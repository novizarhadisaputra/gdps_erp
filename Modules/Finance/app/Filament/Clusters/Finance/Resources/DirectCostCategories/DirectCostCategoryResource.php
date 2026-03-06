<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\DirectCostCategories;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Finance\Filament\Clusters\Finance\FinanceCluster;
use Modules\Finance\Filament\Clusters\Finance\Resources\DirectCostCategories\Pages\CreateDirectCostCategory;
use Modules\Finance\Filament\Clusters\Finance\Resources\DirectCostCategories\Pages\EditDirectCostCategory;
use Modules\Finance\Filament\Clusters\Finance\Resources\DirectCostCategories\Pages\ListDirectCostCategories;
use Modules\Finance\Filament\Clusters\Finance\Resources\DirectCostCategories\Schemas\DirectCostCategoryForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\DirectCostCategories\Tables\DirectCostCategoriesTable;
use Modules\Finance\Models\DirectCostCategory;

class DirectCostCategoryResource extends Resource
{
    protected static ?string $model = DirectCostCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = FinanceCluster::class;

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
