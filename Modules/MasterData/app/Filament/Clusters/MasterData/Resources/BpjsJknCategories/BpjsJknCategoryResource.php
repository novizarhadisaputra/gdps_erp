<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJknCategories;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJknCategories\Pages\CreateBpjsJknCategory;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJknCategories\Pages\EditBpjsJknCategory;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJknCategories\Pages\ListBpjsJknCategories;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJknCategories\Pages\ViewBpjsJknCategory;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJknCategories\Schemas\BpjsJknCategoryForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJknCategories\Tables\BpjsJknCategoriesTable;
use Modules\MasterData\Models\BpjsJknCategory;

class BpjsJknCategoryResource extends Resource
{
    protected static ?string $model = BpjsJknCategory::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Payroll & Benefits';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'BPJS JKN Categories';

    protected static ?string $pluralLabel = 'BPJS JKN Categories';

    protected static ?string $modelLabel = 'BPJS JKN Category';

    public static function form(Schema $schema): Schema
    {
        return BpjsJknCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BpjsJknCategoriesTable::configure($table);
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
            'index' => ListBpjsJknCategories::route('/'),
            'create' => CreateBpjsJknCategory::route('/create'),
            'view' => ViewBpjsJknCategory::route('/{record}'),
            'edit' => EditBpjsJknCategory::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Bpjs Jkn Category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Bpjs Jkn Categories');
    }

    public static function getNavigationLabel(): string
    {
        return __('Bpjs Jkn Categories');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Payroll & Benefits');
    }
}
