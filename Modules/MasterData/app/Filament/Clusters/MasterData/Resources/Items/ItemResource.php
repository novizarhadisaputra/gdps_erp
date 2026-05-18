<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Items;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\Pages\CreateItem;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\Pages\EditItem;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\Pages\ListItems;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\Pages\ViewItem;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\Schemas\ItemForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\Tables\ItemsTable;
use Modules\MasterData\Models\Item;

class ItemResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = Item::class;

    protected static ?int $navigationSort = 80;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|\UnitEnum|null $navigationGroup = 'Products & Inventory';

    public static function form(Schema $schema): Schema
    {
        return ItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItems::route('/'),
            'create' => CreateItem::route('/create'),
            'view' => ViewItem::route('/{record}'),
            'edit' => EditItem::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Item');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Items');
    }

    public static function getNavigationLabel(): string
    {
        return __('Items');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Products & Inventory');
    }
}
