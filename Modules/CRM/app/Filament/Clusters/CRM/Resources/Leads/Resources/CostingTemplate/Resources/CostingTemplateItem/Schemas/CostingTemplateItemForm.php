<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\DepreciationMethod;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\Schemas\ItemForm;
use Modules\MasterData\Models\Item;

class CostingTemplateItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Item Details')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('category')
                                ->options(collect(\Modules\CRM\Enums\CostingCategory::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
                                    ->toArray())
                                ->required()
                                ->live(),
                            Select::make('depreciation_method')
                                ->label('Depreciation Method')
                                ->options(collect(\Modules\CRM\Enums\DepreciationMethod::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
                                    ->toArray())
                                ->default(\Modules\CRM\Enums\DepreciationMethod::StraightLine)
                                ->required()
                                ->live()
                                ->helperText(function (Get $get) {
                                    $itemId = $get('item_id');
                                    if (! $itemId) {
                                        return null;
                                    }
                                    $item = Item::find($itemId);
                                    $ag = $item?->category?->assetGroup;
                                    if (! $ag) {
                                        return null;
                                    }

                                    return "SL Rate: {$ag->rate_straight_line}% | DB Rate: ".($ag->rate_declining_balance ?? 'N/A').'%';
                                })
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::calculate($get, $set)),
                            Select::make('item_id')
                                ->label('Material/Asset')
                                ->relationship('item', 'name')
                                ->createOptionForm(ItemForm::schema())
                                ->editOptionForm(ItemForm::schema())
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    if (! $state) {
                                        return;
                                    }
                                    $item = Item::find($state);
                                    if ($item) {
                                        $set('name', $item->name);
                                        $set('unit_price', $item->price);

                                        $depreciation = $item->depreciation_months;
                                        if (empty($depreciation) || $depreciation <= 0) {
                                            $usefulLifeYears = $item->category?->assetGroup?->useful_life_years;
                                            if ($usefulLifeYears && $usefulLifeYears > 0) {
                                                $depreciation = $usefulLifeYears * 12;
                                            }
                                        }
                                        $set('depreciation_months', $depreciation ?? 1);
                                        self::calculate($get, $set);
                                    }
                                }),
                        ]),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Grid::make(3)
                        ->schema([
                            TextInput::make('quantity')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::calculate($get, $set)),
                            TextInput::make('unit_price')
                                ->numeric()
                                ->prefix('IDR')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::calculate($get, $set)),
                            TextInput::make('depreciation_months')
                                ->label('Depreciation (Months)')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::calculate($get, $set)),
                            TextInput::make('markup_percent')
                                ->label('Markup %')
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::calculate($get, $set)),
                        ]),
                ]),
            Section::make('Calculation Result')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('unit_price_markup')
                                ->label('Price (After Markup)')
                                ->numeric()
                                ->prefix('IDR')
                                ->readOnly()
                                ->dehydrated(),
                            TextInput::make('total_price')
                                ->label('Total Investment')
                                ->numeric()
                                ->prefix('IDR')
                                ->readOnly()
                                ->dehydrated(),
                            TextInput::make('monthly_cost')
                                ->label('Monthly Cost')
                                ->numeric()
                                ->prefix('IDR')
                                ->readOnly()
                                ->dehydrated()
                                ->hint(function (Get $get) {
                                    if ($get('depreciation_method') === DepreciationMethod::DecliningBalance->value) {
                                        return 'Accelerated rate applied.';
                                    }

                                    return 'Standard linear distribution.';
                                }),
                        ]),
                ])->compact(),
        ]);
    }

    protected static function calculate(Get $get, Set $set): void
    {
        $qty = (float) $get('quantity');
        $price = (float) $get('unit_price');
        $markupPercent = (float) $get('markup_percent');
        $deprMonths = (float) $get('depreciation_months');
        $method = $get('depreciation_method');

        $priceAfterMarkup = $price * (1 + ($markupPercent / 100));
        $total = $qty * $priceAfterMarkup;

        $monthly = 0;
        if ($deprMonths > 0) {
            if ($method === DepreciationMethod::DecliningBalance->value) {
                // Get rate from AssetGroup via Item
                $itemId = $get('item_id');
                $item = $itemId ? Item::find($itemId) : null;
                $ag = $item?->category?->assetGroup;
                $rate = (float) ($ag?->rate_declining_balance ?? 0);

                if ($rate > 0) {
                    // DB Monthly Impact = (Total * Rate / 100) / 12
                    $monthly = ($total * $rate / 100) / 12;
                } else {
                    // Fallback to SL if no rate found
                    $monthly = $total / $deprMonths;
                }
            } else {
                // Default Straight Line
                $monthly = $total / $deprMonths;
            }
        } else {
            $monthly = $total;
        }

        $set('unit_price_markup', $priceAfterMarkup);
        $set('total_price', $total);
        $set('monthly_cost', $monthly);
    }
}
