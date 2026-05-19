<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Schemas;

use App\Traits\ParsesCurrency;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\CostingCategory;
use Modules\CRM\Enums\DepreciationMethod;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Schemas\CostingTemplateForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\Schemas\ItemForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\UnitsOfMeasure\Schemas\UnitOfMeasureForm;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\UnitOfMeasure;

class CostingTemplateItemForm
{
    use ParsesCurrency;

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make(__('Item Details'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('category')
                                ->options(collect(CostingCategory::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
                                    ->toArray())
                                ->required()
                                ->live(),
                            Select::make('depreciation_method')
                                ->label(__('Depreciation Method'))
                                ->options(collect(DepreciationMethod::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
                                    ->toArray())
                                ->default(DepreciationMethod::StraightLine)
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
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    self::calculate($get, $set);
                                    CostingTemplateForm::updateTotals($get, $set);
                                }),
                            Select::make('item_id')
                                ->label(__('Material/Asset'))
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
                                    $item = Item::with('unitOfMeasure')->find($state);
                                    if ($item) {
                                        $set('name', $item->name);
                                        $set('unit_price', $item->price);
                                        $set('unit', $item->unitOfMeasure?->name);

                                        $depreciation = $item->depreciation_months;
                                        if (empty($depreciation) || $depreciation <= 0) {
                                            $usefulLifeYears = $item->category?->assetGroup?->useful_life_years;
                                            if ($usefulLifeYears && $usefulLifeYears > 0) {
                                                $depreciation = $usefulLifeYears * 12;
                                            }
                                        }
                                        $set('depreciation_months', $depreciation ?? 1);
                                        self::calculate($get, $set);
                                        CostingTemplateForm::updateTotals($get, $set);
                                    }
                                }),
                        ]),
                    TextInput::make('name')
                        ->required()
                        ->live()
                        ->dehydrated()
                        ->maxLength(255),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('quantity')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    self::calculate($get, $set);
                                    CostingTemplateForm::updateTotals($get, $set);
                                }),
                            Select::make('unit')
                                ->label(__('UOM'))
                                ->placeholder(__('Select or create UoM'))
                                ->options(function () {
                                    return UnitOfMeasure::pluck('name', 'name')->toArray();
                                })
                                ->searchable()
                                ->createOptionForm(UnitOfMeasureForm::schema())
                                ->createOptionUsing(function (array $data): string {
                                    $uom = UnitOfMeasure::create($data);

                                    return $uom->name;
                                })
                                ->live()
                                ->dehydrated(),
                        ]),
                    Grid::make(3)
                        ->schema([

                            TextInput::make('unit_price')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('IDR')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    self::calculate($get, $set);
                                    CostingTemplateForm::updateTotals($get, $set);
                                }),
                            TextInput::make('depreciation_months')
                                ->label(__('Depreciation (Months)'))
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    self::calculate($get, $set);
                                    CostingTemplateForm::updateTotals($get, $set);
                                }),
                            TextInput::make('markup_percent')
                                ->label(__('Markup %'))
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    self::calculate($get, $set);
                                    CostingTemplateForm::updateTotals($get, $set);
                                }),
                        ]),
                    Grid::make(3)
                        ->schema([
                            TextInput::make('unit_price_markup')
                                ->label(__('Price (Markup)'))
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('IDR')
                                ->readOnly()
                                ->dehydrated()
                                ->extraAttributes(['class' => 'bg-gray-50']),
                            TextInput::make('total_price')
                                ->label(__('Subtotal Investment'))
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('IDR')
                                ->readOnly()
                                ->dehydrated()
                                ->extraAttributes(['class' => 'bg-gray-50']),
                            TextInput::make('monthly_cost')
                                ->label(__('Monthly Cost'))
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('IDR')
                                ->readOnly()
                                ->dehydrated()
                                ->extraAttributes(['class' => 'bg-gray-50']),
                        ]),
                ]),
        ];
    }

    protected static function calculate(Get $get, Set $set): void
    {
        $qty = (float) $get('quantity');
        $price = self::parseCurrency($get('unit_price'));
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

        $set('unit_price_markup', round($priceAfterMarkup, 0));
        $set('total_price', round($total, 0));
        $set('monthly_cost', round($monthly, 0));
    }
}
