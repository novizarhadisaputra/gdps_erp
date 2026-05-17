<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Schemas;

use App\Models\User;
use App\Traits\ParsesCurrency;
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
    use ParsesCurrency;

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
                        Grid::make(4)
                            ->schema([
                                TextInput::make('total_cost_amount')
                                    ->label('Total Cost (Modal)')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR ')
                                    ->readOnly()
                                    ->dehydrated()
                                    ->afterStateHydrated(function (Get $get, Set $set) {
                                        self::updateTotals($get, $set);
                                    })
                                    ->extraAttributes(['class' => 'bg-gray-50']),
                                TextInput::make('total_amount')
                                    ->label('Total Investment')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR ')
                                    ->readOnly()
                                    ->dehydrated()
                                    ->afterStateHydrated(function (Get $get, Set $set) {
                                        self::updateTotals($get, $set);
                                    }),
                                TextInput::make('total_monthly_cost')
                                    ->label('Total Monthly Cost')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR ')
                                    ->readOnly()
                                    ->dehydrated()
                                    ->afterStateHydrated(function (Get $get, Set $set) {
                                        self::updateTotals($get, $set);
                                    }),
                                TextInput::make('margin_percentage')
                                    ->label('Initial Margin')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->readOnly()
                                    ->afterStateHydrated(function (Get $get, Set $set) {
                                        self::updateTotals($get, $set);
                                    })
                                    ->extraAttributes(['class' => 'bg-gray-50 font-bold text-primary-600']),
                            ]),
                        Textarea::make('notes')
                            ->label('Finance Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ])->columnSpanFull(),
        ];
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $items = $get('costingTemplateItems') ?? [];
        $totalAmount = 0;
        $totalCost = 0;
        $totalMonthly = 0;

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $costPrice = self::parseCurrency($item['unit_price'] ?? 0);
            $markupPercent = (float) ($item['markup_percent'] ?? 0);
            $deprMonths = (float) ($item['depreciation_months'] ?? 1);
            $deprMonths = $deprMonths > 0 ? $deprMonths : 1;
            $method = $item['depreciation_method'] ?? DepreciationMethod::StraightLine->value;

            // Recalculate based on cost and markup to be 100% sure
            $sellingPrice = $costPrice * (1 + ($markupPercent / 100));
            $subtotalCost = $qty * $costPrice;
            $subtotalSelling = $qty * $sellingPrice;

            // Calculate monthly cost based on depreciation method
            $monthly = 0;
            if ($method === DepreciationMethod::DecliningBalance->value) {
                $itemId = $item['item_id'] ?? null;
                $dbItem = $itemId ? Item::find($itemId) : null;
                $ag = $dbItem?->category?->assetGroup;
                $rate = (float) ($ag?->rate_declining_balance ?? 0);

                if ($rate > 0) {
                    $monthly = ($subtotalSelling * $rate / 100) / 12;
                } else {
                    $monthly = $subtotalSelling / $deprMonths;
                }
            } else {
                $monthly = $subtotalSelling / $deprMonths;
            }

            $totalCost += $subtotalCost;
            $totalAmount += $subtotalSelling;
            $totalMonthly += $monthly;
        }

        // Apply rounded values to the summary fields
        $set('total_cost_amount', round($totalCost, 0));
        $set('total_amount', round($totalAmount, 0));
        $set('total_monthly_cost', round($totalMonthly, 0));

        if ($totalAmount > 0) {
            $margin = (($totalAmount - $totalCost) / $totalAmount) * 100;
            $set('margin_percentage', round($margin, 2));
        } else {
            $set('margin_percentage', 0);
        }
    }
}
