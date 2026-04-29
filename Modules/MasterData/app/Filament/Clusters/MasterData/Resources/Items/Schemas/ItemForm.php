<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ItemCategories\Schemas\ItemCategoryForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\UnitsOfMeasure\Schemas\UnitOfMeasureForm;
use Modules\MasterData\Models\AssetGroup;
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
                ->description('Specify the core information for this inventory or asset item, including its classification and pricing.')
                ->schema([
                    TextInput::make('code')
                        ->label('Item Code')
                        ->required()
                        ->unique(Item::class, 'code', ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('e.g. ITM-001')
                        ->helperText('Unique identification code for this item.'),
                    TextInput::make('name')
                        ->label('Item Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. Laptop Dell XPS')
                        ->helperText('The descriptive name of the item.'),
                    Select::make('item_category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select category')
                        ->helperText('The primary classification for this item.')
                        ->createOptionForm(ItemCategoryForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (! $state) {
                                return;
                            }
                            $category = ItemCategory::find($state);
                            if ($category && $category->assetGroup) {
                                $set('asset_group_id', $category->asset_group_id);
                                $set('depreciation_months', $category->assetGroup->useful_life_years * 12);
                            }
                        }),
                    Select::make('asset_group_id')
                        ->label('Asset Group (Default)')
                        ->relationship('assetGroup', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Select asset group')
                        ->helperText('Override the asset group if this item follows a different depreciation schedule.')
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (! $state) {
                                return;
                            }
                            $group = AssetGroup::find($state);
                            if ($group) {
                                $set('depreciation_months', $group->useful_life_years * 12);
                            }
                        }),
                    Select::make('unit_of_measure_id')
                        ->label('Unit of Measure')
                        ->relationship('unitOfMeasure', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select unit')
                        ->helperText('The standard unit used for quantity (e.g., Pcs, Unit).')
                        ->createOptionForm(UnitOfMeasureForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver()),
                    Textarea::make('description')
                        ->label('Description')
                        ->placeholder('Detailed specifications or notes about the item...')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    TextInput::make('price')
                        ->label('Standard Price')
                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                        ->prefix('IDR')
                        ->placeholder('0')
                        ->helperText('The default purchase or market price for this item.'),
                    TextInput::make('depreciation_months')
                        ->label('Depreciation (Months)')
                        ->numeric()
                        ->placeholder('e.g. 48')
                        ->helperText('The standard useful life period in months as per accounting standards.'),
                    DatePicker::make('price_valid_at')
                        ->label('Price Effective Date')
                        ->placeholder('Select date')
                        ->default(now())
                        ->helperText('The date from which the standard price is applicable.')
                        ->required(),
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->required()
                        ->default(true)
                        ->helperText('Turn off to hide this item from selection lists.'),
                    SpatieMediaLibraryFileUpload::make('image')
                        ->collection('image')
                        ->label('Item Photo')
                        ->placeholder('Upload item image')
                        ->image()
                        ->visibility('private')
                        ->columnSpanFull()
                        ->helperText('A visual representation of the item (JPG/PNG).'),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('Regional Pricing Overrides')
                ->description('Specify different prices for specific project areas or locations.')
                ->schema([
                    Repeater::make('itemPrices')
                        ->label('')
                        ->schema([
                            Select::make('project_area_id')
                                ->label('Project Area')
                                ->relationship('projectArea', 'name')
                                ->createOptionForm(ProjectAreaForm::schema())
                                ->createOptionAction(fn (Action $action) => $action->slideOver())
                                ->required()
                                ->searchable()
                                ->distinct(),
                            TextInput::make('price')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('IDR ')
                                ->required(),
                        ])
                        ->columnSpanFull()
                        ->grid(2)
                        ->defaultItems(0),
                ]),
        ];
    }
}
