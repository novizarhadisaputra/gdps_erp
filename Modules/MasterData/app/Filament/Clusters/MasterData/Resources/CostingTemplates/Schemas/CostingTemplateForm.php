<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Modules\MasterData\Enums\CostingCategory;
use Modules\MasterData\Models\AssetGroup;
use Modules\MasterData\Models\Item;

class CostingTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Wizard::make([
                Step::make('Template Details')
                    ->description('Define basic template identification.')
                    ->icon('heroicon-m-identification')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),

                Step::make('Items & Costing')
                    ->description('Add items and configure cost/markup.')
                    ->icon('heroicon-m-shopping-cart')
                    ->schema([
                        Repeater::make('costingTemplateItems')
                            ->relationship()
                            ->schema([
                                Select::make('item_id')
                                    ->label('Item (Master)')
                                    ->options(Item::where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $item = Item::find($state);
                                            if ($item) {
                                                $set('name', $item->name);
                                                $set('unit', $item->unitOfMeasure?->name ?? 'Unit');
                                                $set('unit_price', (int) $item->price);
                                                $set('depreciation_months', (int) $item->depreciation_months);

                                                $assetGroupId = $item->asset_group_id ?? $item->category?->asset_group_id;
                                                $set('asset_group_id', $assetGroupId);

                                                if ($assetGroupId) {
                                                    $group = AssetGroup::find($assetGroupId);
                                                    $set('useful_life_years', $group?->useful_life_years ?? 0);
                                                } else {
                                                    $set('useful_life_years', 0);
                                                }

                                                // Trigger validations
                                                $set('markup_percent', 0);
                                            }
                                        }
                                    }),

                                Textarea::make('name')
                                    ->label('Item Name / Description')
                                    ->required()
                                    ->rows(2),

                                Select::make('category')
                                    ->label('Cost Category')
                                    ->options(CostingCategory::class)
                                    ->required(),

                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateTotals($set, $get)),

                                TextInput::make('unit_price')
                                    ->label('Base Price')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->prefix('IDR')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMarkup($set, $get)),

                                TextInput::make('markup_percent')
                                    ->label('Markup (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMarkup($set, $get)),

                                TextInput::make('unit_price_markup')
                                    ->label('Price (After Markup)')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->prefix('IDR')
                                    ->readOnly()
                                    ->dehydrated(),

                                TextInput::make('total_price')
                                    ->label('Total Price')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->prefix('IDR')
                                    ->readOnly()
                                    ->dehydrated(),

                                Select::make('asset_group_id')
                                    ->label('Asset Group (Depreciation)')
                                    ->options(AssetGroup::pluck('name', 'id'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $group = AssetGroup::find($state);
                                            $set('useful_life_years', $group?->useful_life_years ?? 0);
                                        }
                                    }),

                                TextInput::make('useful_life_years')
                                    ->label('Life (Years)')
                                    ->numeric()
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $set('depreciation_months', (int) $state * 12);
                                    }),

                                TextInput::make('depreciation_months')
                                    ->label('Depr. (Months)')
                                    ->numeric()
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMonthly($set, $get)),

                                TextInput::make('monthly_cost')
                                    ->label('Monthly Cost')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->prefix('IDR')
                                    ->readOnly()
                                    ->dehydrated(),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Add Item to Costing'),
                    ]),

                Step::make('Cost Simulation')
                    ->description('Review projected financial impact.')
                    ->icon('heroicon-m-calculator')
                    ->schema([
                        TextEntry::make('simulation')
                            ->label('Costing Summary')
                            ->html()
                            ->state(function (Get $get) {
                                $items = $get('costingTemplateItems') ?? [];
                                if (empty($items)) {
                                    return new HtmlString('<div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">No items added yet.</div>');
                                }

                                $totalBase = 0;
                                $totalMarkup = 0;
                                $totalMonthly = 0;
                                $rows = '';

                                foreach ($items as $item) {
                                    $qty = (int) ($item['quantity'] ?? 1);
                                    $base = (float) ($item['unit_price'] ?? 0);
                                    $markupPrice = (float) ($item['unit_price_markup'] ?? 0);
                                    $monthly = (float) ($item['monthly_cost'] ?? 0);

                                    $subBase = $base * $qty;
                                    $subMarkup = $markupPrice * $qty;

                                    $totalBase += $subBase;
                                    $totalMarkup += $subMarkup;
                                    $totalMonthly += $monthly;

                                    $fmt = fn ($val) => number_format($val, 0, ',', '.');
                                    $itemName = $item['name'] ?? 'Unnamed Item';

                                    $rows .= "
                                        <tr class='border-b hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors'>
                                            <td class='px-4 py-3 font-medium text-gray-900 dark:text-gray-100'>{$itemName}</td>
                                            <td class='px-4 py-3 text-center'>{$qty}</td>
                                            <td class='px-4 py-3 text-right'>Rp {$fmt($base)}</td>
                                            <td class='px-4 py-3 text-right text-primary-600 font-medium'>Rp {$fmt($markupPrice)}</td>
                                            <td class='px-4 py-3 text-right font-bold'>Rp {$fmt($monthly)}</td>
                                        </tr>
                                    ";
                                }

                                $fmt = fn ($val) => number_format($val, 0, ',', '.');

                                return new HtmlString("
                                    <div class='relative overflow-x-auto shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700'>
                                        <table class='w-full text-sm text-left text-gray-500 dark:text-gray-400'>
                                            <thead class='text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400'>
                                                <tr>
                                                    <th scope='col' class='px-4 py-3'>Item Name</th>
                                                    <th scope='col' class='px-4 py-3 text-center'>Qty</th>
                                                    <th scope='col' class='px-4 py-3 text-right'>Base/Unit</th>
                                                    <th scope='col' class='px-4 py-3 text-right'>Markup/Unit</th>
                                                    <th scope='col' class='px-4 py-3 text-right'>Monthly Impact</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {$rows}
                                            </tbody>
                                            <tfoot>
                                                <tr class='font-bold text-gray-900 dark:text-white bg-gray-100/50 dark:bg-gray-800/50'>
                                                    <td colspan='4' class='px-4 py-4 text-right uppercase tracking-wider'>Total Monthly Cost Impact</td>
                                                    <td class='px-4 py-4 text-right text-lg text-primary-600'>Rp {$fmt($totalMonthly)}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                ");
                            }),
                    ]),
            ])->columnSpanFull()->persistStepInQueryString(),
        ];
    }

    public static function calculateMarkup(Set $set, Get $get): void
    {
        $basePrice = (float) $get('unit_price');
        $percent = (float) $get('markup_percent');

        $priceMarkup = $basePrice + ($basePrice * ($percent / 100));
        $set('unit_price_markup', $priceMarkup);

        self::calculateTotals($set, $get);
    }

    public static function calculateTotals(Set $set, Get $get): void
    {
        $qty = (int) $get('quantity');
        $priceMarkup = (float) $get('unit_price_markup');

        $total = $qty * $priceMarkup;
        $set('total_price', $total);

        self::calculateMonthly($set, $get);
    }

    public static function calculateMonthly(Set $set, Get $get): void
    {
        $total = (float) $get('total_price');
        $depreciationMonths = (int) $get('depreciation_months');
        $years = (int) $get('useful_life_years');
        if ($depreciationMonths > 0 && $years > 0) {
            $months = $depreciationMonths;
            $monthly = $total / $months;
            $set('monthly_cost', round($monthly, 2));
        } else {
            // If expense (0 years), full cost is charged monthly? Or just 0 depreciation?
            // Assuming for now if not asset, it might be monthly expense directly?
            // Based on Excel "Beban Per Bulan" seems to come from depreciation.
            // If years = 0, monthly cost = total (expense)? Or 0?
            // Let's assume standard Asset logic: 0 means Expense (One time charge? or Monthly Recurring?)
            // Excel sample: Tools has depreciation. Material usually is expense.
            // If Material, usually Monthly Cost = Total Price (if consumed monthly).
            // Let's default to Total Price if Years=0 (Expense), but allow manual edit? No, strictly calculated?
            // For now, if years=0, set monthly=total. User can adjust quantity if it's per month consumption.
            $set('monthly_cost', $total);
        }
    }
}
