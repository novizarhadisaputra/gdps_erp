<?php

namespace Modules\MasterData\Filament\Resources\Items\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Resources\ItemCategories\Schemas\ItemCategoryForm;
use Modules\MasterData\Filament\Resources\UnitsOfMeasure\Schemas\UnitOfMeasureForm;
use Modules\MasterData\Models\ItemCategory;
use Modules\MasterData\Models\UnitOfMeasure;

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
            TextInput::make('code')
                ->required()
                ->unique(ignoreRecord: true, table: 'items')
                ->maxLength(255)
                ->placeholder('ITM001'),
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Laptop Dell XPS'),
            Select::make('item_category_id')
                ->label('Category')
                ->options(fn () => ItemCategory::query()->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->preload()
                ->createOptionForm(ItemCategoryForm::schema())
                ->createOptionUsing(fn (array $data) => ItemCategory::create($data)->id),
            Select::make('unit_of_measure_id')
                ->label('Unit of Measure')
                ->options(fn () => UnitOfMeasure::query()->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->preload()
                ->createOptionForm(UnitOfMeasureForm::schema())
                ->createOptionUsing(fn (array $data) => UnitOfMeasure::create($data)->id),
            Textarea::make('description')
                ->maxLength(65535)
                ->columnSpanFull(),
            TextInput::make('price')
                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                ->helperText('Standard price for this item.'),
            TextInput::make('depreciation_rate')
                ->numeric()
                ->suffix('%')
                ->helperText('Yearly depreciation rate.'),
            Toggle::make('is_active')
                ->required()
                ->default(true),
            \Filament\Forms\Components\Repeater::make('itemPrices')
                ->relationship()
                ->schema([
                    Select::make('project_area_id')
                        ->label('Project Area')
                        ->options(fn () => \Modules\MasterData\Models\ProjectArea::pluck('name', 'id'))
                        ->required()
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
