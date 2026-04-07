<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Schemas;

use App\Models\User;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\DepreciationMethod;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Schemas\CostingTemplateItemForm;
use Modules\CRM\Livewire\CostingTemplate\ManageCostingItems;
use Modules\CRM\Models\Lead;
use Modules\MasterData\Models\Item;

class CostingTemplateForm
{
    public static function getAutoFillData(Lead $lead): array
    {
        $latestGi = $lead->generalInformations()->latest('created_at')->first();

        return [
            'name' => $lead->customer?->name.' Tools & Equipment',
            'description' => $latestGi?->scope_of_work,
            'pic_id' => $lead->pic_costing_id ?? auth()->id(),
            'work_scheme_id' => $latestGi?->work_scheme_id ?? $lead->work_scheme_id,
            'project_area_id' => $latestGi?->project_area_id ?? $lead->project_area_id,
            'costingTemplateItems' => [],
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::schema());
    }

    public static function schema(): array
    {
        return [
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

                Step::make('Tools & Equipment Costing')
                    ->description('Manage items and calculations')
                    ->schema([
                        Livewire::make(ManageCostingItems::class)
                            ->lazy()
                            ->visible(fn (string $operation): bool => $operation === 'edit'),

                        Repeater::make('costingTemplateItems')
                            ->relationship()
                            ->visible(fn (string $operation): bool => $operation === 'create')
                            ->schema(CostingTemplateItemForm::schema())
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
                        Grid::make(3)
                            ->schema([
                                TextInput::make('total_amount')
                                    ->label('Total Investment')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR ')
                                    ->readOnly()
                                    ->dehydrated()
                                    ->afterStateHydrated(function ($record, Set $set, string $operation) {
                                        if ($operation === 'edit' && $record) {
                                            $set('total_amount', $record->costingTemplateItems()->sum('total_price') ?? 0);
                                        }
                                    }),
                                TextInput::make('total_monthly_cost')
                                    ->label('Total Monthly Cost')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR ')
                                    ->readOnly()
                                    ->dehydrated()
                                    ->afterStateHydrated(function ($record, Set $set, string $operation) {
                                        if ($operation === 'edit' && $record) {
                                            $set('total_monthly_cost', $record->costingTemplateItems()->sum('monthly_cost') ?? 0);
                                        }
                                    }),
                                TextInput::make('margin_percentage')
                                    ->label('Initial Margin')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->readOnly()
                                    ->afterStateHydrated(function ($record, Set $set, string $operation) {
                                        if ($operation === 'edit' && $record) {
                                            $set('margin_percentage', $record->margin_percentage ?? 0);
                                        }
                                    })
                                    ->extraAttributes(['class' => 'bg-gray-50']),
                            ]),
                        Textarea::make('notes')
                            ->label('Finance Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ])->columnSpanFull(),
        ];
    }

    protected static function updateTotals(Get $get, Set $set): void
    {
        $items = $get('costingTemplateItems') ?: [];
        $totalAmount = 0;
        $totalCost = 0;
        $totalMonthly = 0;

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $price = self::parseCurrency($item['unit_price'] ?? 0);
            $totalPrice = self::parseCurrency($item['total_price'] ?? 0);

            $totalAmount += $totalPrice;
            $totalCost += ($qty * $price);
            $totalMonthly += self::parseCurrency($item['monthly_cost'] ?? 0);
        }

        $set('total_amount', $totalAmount);
        $set('total_monthly_cost', $totalMonthly);

        if ($totalAmount > 0) {
            $margin = (($totalAmount - $totalCost) / $totalAmount) * 100;
            $set('margin_percentage', round($margin, 2));
        } else {
            $set('margin_percentage', 0);
        }
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
            $item = $itemId ? Item::find($itemId) : null;
            $ag = $item?->assetGroup ?? $item?->category?->assetGroup;

            if ($methodValue === DepreciationMethod::DecliningBalance->value) {
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
