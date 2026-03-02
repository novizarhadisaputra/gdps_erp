<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Schemas;

use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Lead;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\Schemas\ItemForm;

class CostingTemplateForm
{
    public static function getAutoFillData(Lead $lead): array
    {
        $latestGi = $lead->generalInformations()->latest()->first();

        return [
            'name' => $lead->customer?->name,
            'description' => $latestGi?->scope_of_work,
            'pic_id' => $lead->pic_costing_id ?? auth()->id(),
            'costingTemplateItems' => [],
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([
                Step::make('Core Information')
                    ->description('Basic template details')
                    ->schema([
                        TextInput::make('code')
                            ->hidden(fn (string $operation): bool => $operation === 'create')
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('pic_id')
                            ->label('Costing PIC')
                            ->relationship('pic', 'name')
                            ->options(function () {
                                return User::where('id', auth()->id())
                                    ->orWhere('unit_id', '10000016')
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(auth()->id()),
                        TextInput::make('description')
                            ->maxLength(255),
                    ]),

                Step::make('Items & Costing')
                    ->description('Manage items and calculations')
                    ->schema([
                        Placeholder::make('items_hint')
                            ->label('')
                            ->content(function () {
                                return new \Illuminate\Support\HtmlString('
                                    <div class="p-4 bg-primary-50 rounded-lg border border-primary-200 text-primary-900">
                                        Saat ini Anda sedang dalam mode <strong>Edit</strong>. Untuk mengelola item costing secara rinci dan aman (menghindari memory crash), silakan gunakan tab <strong>"Cost Items"</strong> di menu samping.
                                    </div>
                                ');
                            })
                            ->visible(fn (string $operation): bool => $operation !== 'create'),

                        \Filament\Forms\Components\Repeater::make('costingTemplateItems')
                            ->relationship()
                            ->visible(fn (string $operation): bool => $operation === 'create')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make()
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        Select::make('category')
                                                            ->options(collect(\Modules\CRM\Enums\CostingCategory::cases())
                                                                ->filter(fn ($case) => $case !== \Modules\CRM\Enums\CostingCategory::Manpower)
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
                                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                                self::calculateItem($get, $set);
                                                            }),
                                                    ]),
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
                                                        $item = \Modules\MasterData\Models\Item::find($state);
                                                        if ($item) {
                                                            $set('name', $item->name);
                                                            $set('unit_price', $item->price);

                                                            $depreciation = $item->depreciation_months;
                                                            if (empty($depreciation) || $depreciation <= 0) {
                                                                $assetGroup = $item->assetGroup ?? $item->category?->assetGroup;
                                                                $usefulLifeYears = $assetGroup?->useful_life_years;
                                                                if ($usefulLifeYears && $usefulLifeYears > 0) {
                                                                    $depreciation = $usefulLifeYears * 12;
                                                                }
                                                            }
                                                            $set('depreciation_months', $depreciation ?? 1);
                                                            self::calculateItem($get, $set);
                                                        }
                                                    }),
                                                TextInput::make('name')
                                                    ->label('Item Name (Override)')
                                                    ->required()
                                                    ->maxLength(255),
                                            ])->columnSpan(1),

                                        Section::make()
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('quantity')
                                                            ->numeric()
                                                            ->default(1)
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                                self::calculateItem($get, $set);
                                                            }),
                                                        TextInput::make('unit_price')
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                            ->prefix('IDR')
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                                self::calculateItem($get, $set);
                                                            })
                                                            ->dehydrateStateUsing(fn ($state) => self::parseCurrency($state)),
                                                        TextInput::make('markup_percent')
                                                            ->label('Markup %')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                                self::calculateItem($get, $set, 'markup_percent');
                                                            }),
                                                        TextInput::make('depreciation_months')
                                                            ->label('Depreciation (Mo)')
                                                            ->numeric()
                                                            ->default(1)
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                                self::calculateItem($get, $set);
                                                            })
                                                            ->helperText('Use 1 for monthly recurring costs.'),
                                                    ]),
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('total_price')
                                                            ->label('Investment')
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                            ->prefix('IDR')
                                                            ->readOnly()
                                                            ->dehydrated()
                                                            ->extraAttributes(['class' => 'font-bold']),
                                                        TextInput::make('monthly_cost')
                                                            ->label('Monthly Impact')
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                            ->prefix('IDR')
                                                            ->readOnly()
                                                            ->dehydrated()
                                                            ->extraAttributes(['class' => 'font-bold']),
                                                    ]),
                                            ])->columnSpan(1),
                                    ]),
                            ])
                            ->itemLabel(fn (array $state): ?string => ($state['category'] ?? 'Item').': '.($state['name'] ?? 'Untitled'))
                            ->defaultItems(0)
                            ->columnSpanFull()
                            ->addActionLabel('Add New Item')
                            ->collapsible()
                            ->collapsed(fn (string $operation) => $operation !== 'create')
                            ->cloneable()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            })
                            ->afterStateHydrated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            }),
                    ]),

                Step::make('Costing Summary')
                    ->description('Review totals and submit')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_amount')
                                    ->label('Total Investment')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR')
                                    ->readOnly()
                                    ->dehydrated(),
                                TextInput::make('total_monthly_cost')
                                    ->label('Total Monthly Cost')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR')
                                    ->readOnly()
                                    ->dehydrated(),
                            ]),
                    ]),
            ])->columnSpanFull(),
        ]);
    }

    protected static function updateTotals(Get $get, Set $set): void
    {
        $items = $get('costingTemplateItems') ?: [];
        $totalAmount = 0;
        $totalMonthly = 0;
        foreach ($items as $item) {
            $totalAmount += self::parseCurrency($item['total_price'] ?? 0);
            $totalMonthly += self::parseCurrency($item['monthly_cost'] ?? 0);
        }
        $set('total_amount', $totalAmount);
        $set('total_monthly_cost', $totalMonthly);
    }

    protected static function calculateItem(Get $get, Set $set, ?string $trigger = null): void
    {
        $qty = $get('quantity');
        $price = $get('unit_price');
        $markupPercent = $get('markup_percent');
        $priceAfterMarkup = $get('unit_price_markup');
        $deprMonths = $get('depreciation_months');
        $method = $get('depreciation_method');

        $qty = is_numeric($qty) ? (float) $qty : 0;
        $price = self::parseCurrency($price);
        $markupPercent = is_numeric($markupPercent) ? (float) $markupPercent : 0;
        $priceAfterMarkup = self::parseCurrency($priceAfterMarkup);
        $deprMonths = is_numeric($deprMonths) ? (float) $deprMonths : 1;

        if ($trigger === 'unit_price_markup') {
            if ($price > 0) {
                $markupPercent = (($priceAfterMarkup / $price) - 1) * 100;
                $set('markup_percent', round($markupPercent, 2));
            }
        } elseif ($trigger === 'markup_percent' || empty($priceAfterMarkup)) {
            $priceAfterMarkup = $price * (1 + ($markupPercent / 100));
            $set('unit_price_markup', $priceAfterMarkup);
        } else {
            // Default: recalculate based on markup percent if both exist
            $priceAfterMarkup = $price * (1 + ($markupPercent / 100));
            $set('unit_price_markup', $priceAfterMarkup);
        }

        $total = $qty * $priceAfterMarkup;

        $monthly = 0;
        if ($deprMonths > 0) {
            $methodValue = $method instanceof \BackedEnum ? $method->value : $method;

            $itemId = $get('item_id');
            $item = $itemId ? \Modules\MasterData\Models\Item::find($itemId) : null;
            $ag = $item?->assetGroup ?? $item?->category?->assetGroup;

            if ($methodValue === \Modules\CRM\Enums\DepreciationMethod::DecliningBalance->value) {
                $rate = (float) ($ag?->rate_declining_balance ?? 0);

                if ($rate > 0) {
                    $monthly = ($total * $rate / 100) / 12;
                } else {
                    $monthly = $total / $deprMonths;
                }
            } else {
                // Straight Line
                $rate = (float) ($ag?->rate_straight_line ?? 0);

                if ($rate > 0) {
                    $monthly = ($total * $rate / 100) / 12;
                } else {
                    $monthly = $total / $deprMonths;
                }
            }
        } else {
            $monthly = $total;
        }

        $set('total_price', $total);
        $set('monthly_cost', $monthly);
    }

    protected static function parseCurrency($value): float
    {
        if (! $value) {
            return 0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = str_replace('.', '', $value);
        $clean = str_replace(',', '.', $clean);

        return (float) $clean;
    }
}
