<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
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
            Section::make('Template Details')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ]),

            Section::make('Items & Costing')
                ->columnSpanFull()
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
                                            $set('unit_price', $item->price);

                                            $assetGroupId = $item->category?->asset_group_id;
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

                            TextArea::make('name')
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
        $years = (int) $get('useful_life_years');

        if ($years > 0) {
            $months = $years * 12;
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
