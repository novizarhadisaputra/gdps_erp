<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Resources\Customers\Schemas\CustomerForm;
use Modules\MasterData\Filament\Resources\ProductClusters\Schemas\ProductClusterForm;
use Modules\MasterData\Filament\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Filament\Resources\Taxes\Schemas\TaxForm;
use Modules\MasterData\Filament\Resources\WorkSchemes\Schemas\WorkSchemeForm;
use Modules\MasterData\Models\Item;

class ProfitabilityAnalysisForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('general_information_id')
                    ->relationship('generalInformation', 'id')
                    ->label('GI Form (RR Submission)')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->helperText('Customer associated with the project.')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm(CustomerForm::schema()),

                Section::make('Project Code Parameters')
                    ->columns(columns: 1)
                    ->schema([
                        Select::make('work_scheme_id')
                            ->relationship('workScheme', 'name')
                            ->helperText('The work scheme for this project.')
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Defines the operational scheme.')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm(WorkSchemeForm::schema()),
                        Select::make('product_cluster_id')
                            ->relationship('productCluster', 'name')
                            ->helperText('Cluster of the product/service.')
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Group of related products.')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm(ProductClusterForm::schema()),
                        Select::make('tax_id')
                            ->relationship('tax', 'name')
                            ->helperText('Applicable tax regulation.')
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Tax rules for this project.')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm(TaxForm::schema()),
                        Select::make('project_area_id')
                            ->relationship('projectArea', 'name')
                            ->helperText('Geographical area of the project.')
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Location where project is executed.')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->createOptionForm(ProjectAreaForm::schema()),
                    ]),

                Section::make('Financial Analysis')
                    ->schema([
                        TextInput::make('revenue_per_month')
                            ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                            ->helperText('Estimated monthly revenue. Contoh: 80,000,000')
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Total revenue expected per month.')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $get, $set) => self::calculateMargin($state, $get('direct_cost'), $set)),
                        TextInput::make('direct_cost')
                            ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                            ->helperText('Direct financial costs.')
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Auto-calculated from Manpower and Materials.')
                            ->required()
                            ->readOnly()
                            ->live()
                            ->afterStateUpdated(fn ($state, $get, $set) => self::calculateMargin($get('revenue_per_month'), $state, $set)),
                        TextInput::make('management_fee')
                            ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                            ->helperText('Fee for management services.'),
                        TextInput::make('margin_percentage')
                            ->numeric()
                            ->suffix('%')
                            ->helperText('Calculated profit margin.')
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: '(Revenue - Cost) / Revenue * 100')
                            ->readOnly()
                            ->placeholder('Auto-calculated'),
                    ])->columns(columns: 1),

                Section::make('Costing Details')
                    ->headerActions([
                        Action::make('Import from Akurat')
                            ->icon('heroicon-o-arrow-path')
                            ->action(fn () => Notification::make()->title('Akurat Sync is not implemented yet.')->warning()->send()),
                    ])
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Select::make('item_id')
                                    ->label('Item')
                                    ->relationship('item', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if (! $state) {
                                            return;
                                        }
                                        $item = Item::find($state);
                                        if ($item) {
                                            $set('unit_cost_price', $item->price);
                                            $set('depreciation_months', $item->depreciation_months ?? 1);
                                        }
                                    })
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                TextInput::make('unit_cost_price')
                                    ->label('Modal Price')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                TextInput::make('depreciation_months')
                                    ->label('Depreciation (Mo)')
                                    ->numeric()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                TextInput::make('markup_percentage')
                                    ->label('Markup (%)')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                TextInput::make('total_monthly_cost')
                                    ->label('Modal/Mo')
                                    ->disabled()
                                    ->dehydrated()
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->placeholder(fn (Get $get) => self::calculateItemMonthlyCost($get))
                                    ->columnSpan(1),
                                TextInput::make('total_monthly_sale')
                                    ->label('Selling/Mo')
                                    ->disabled()
                                    ->dehydrated()
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->placeholder(fn (Get $get) => self::calculateItemMonthlySale($get))
                                    ->columnSpan(1),
                            ])
                            ->columns(8)
                            ->columnSpanFull()
                            ->itemLabel(fn (array $state): ?string => Item::find($state['item_id'] ?? null)?->name ?? 'New Item')
                            ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                    ]),
            ]);
    }

    protected static function calculateDirectCost($get, $set): void
    {
        $items = $get('items') ?? [];
        $totalDirectCost = 0;
        $totalRevenue = 0;

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $costPrice = (float) ($item['unit_cost_price'] ?? 0);
            $deprMonths = (float) ($item['depreciation_months'] ?? 1);
            $markup = (float) ($item['markup_percentage'] ?? 0);

            if ($deprMonths <= 0) {
                $deprMonths = 1;
            }

            $monthlyCost = ($costPrice / $deprMonths) * $qty;
            $monthlySale = $monthlyCost * (1 + ($markup / 100));

            $totalDirectCost += $monthlyCost;
            $totalRevenue += $monthlySale;
        }

        $set('direct_cost', $totalDirectCost);
        $set('revenue_per_month', $totalRevenue);

        // Recalculate margin
        self::calculateMargin($totalRevenue, $totalDirectCost, $set);
    }

    public static function calculateItemMonthlyCost(Get $get): float
    {
        $qty = (float) ($get('quantity') ?? 0);
        $costPrice = (float) ($get('unit_cost_price') ?? 0);
        $deprMonths = (float) ($get('depreciation_months') ?? 1);

        if ($deprMonths <= 0) {
            $deprMonths = 1;
        }

        return ($costPrice / $deprMonths) * $qty;
    }

    public static function calculateItemMonthlySale(Get $get): float
    {
        $monthlyCost = self::calculateItemMonthlyCost($get);
        $markup = (float) ($get('markup_percentage') ?? 0);

        return $monthlyCost * (1 + ($markup / 100));
    }

    protected static function calculateMargin($revenue, $cost, $set): void
    {
        $revenue = (float) ($revenue ?? 0);
        $cost = (float) ($cost ?? 0);

        if ($revenue > 0) {
            $margin = (($revenue - $cost) / $revenue) * 100;
            $set('margin_percentage', round($margin, 2));
        } else {
            $set('margin_percentage', 0);
        }
    }
}
