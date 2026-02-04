<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ItemCategories\Schemas\ItemCategoryForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\UnitsOfMeasure\Schemas\UnitOfMeasureForm;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\ItemCategory;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Item Details')
                ->schema([
                    TextInput::make('code')
                        ->required()
                        ->unique(Item::class, 'code', ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('ITM001'),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Laptop Dell XPS'),
                    Select::make('item_category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm(ItemCategoryForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (! $state) {
                                return;
                            }
                            $category = ItemCategory::find($state);
                            if ($category && $category->assetGroup) {
                                $set('depreciation_months', $category->assetGroup->useful_life_years * 12);
                            }
                        }),
                    Select::make('unit_of_measure_id')
                        ->label('Unit of Measure')
                        ->relationship('unitOfMeasure', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm(UnitOfMeasureForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver()),
                    Textarea::make('description')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    TextInput::make('price')
                        ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                        ->helperText('Standard price for this item.'),
                    TextInput::make('depreciation_months')
                        ->numeric()
                        ->label('Depreciation (Months)')
                        ->helperText('Standard depreciation period in months (PSAK).'),
                    DatePicker::make('price_valid_at')
                        ->label('Price Valid')
                        ->default(now())
                        ->required(),
                    Toggle::make('is_active')
                        ->required()
                        ->default(true),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Repeater::make('itemPrices')
                ->relationship()
                ->schema([
                    Select::make('project_area_id')
                        ->label('Project Area')
                        ->relationship('projectArea', 'name')
                        ->createOptionForm(\Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->required()
                        ->searchable()
                        ->distinct(),
                    TextInput::make('price')
                        ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                        ->required(),
                ])
                ->columnSpanFull()
                ->grid(2)
                ->defaultItems(0),
        ];
    }
}
