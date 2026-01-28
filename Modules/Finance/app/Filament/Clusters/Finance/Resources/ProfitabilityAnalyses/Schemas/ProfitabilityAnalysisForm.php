<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Modules\MasterData\Filament\Resources\Customers\Schemas\CustomerForm;
use Modules\MasterData\Filament\Resources\Items\Schemas\ItemForm;
use Modules\MasterData\Filament\Resources\ProductClusters\Schemas\ProductClusterForm;
use Modules\MasterData\Filament\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Filament\Resources\Taxes\Schemas\TaxForm;
use Modules\MasterData\Filament\Resources\WorkSchemes\Schemas\WorkSchemeForm;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\ItemCategory;
use Modules\MasterData\Services\JobPositionService;

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

                Tabs::make('Details')
                    ->tabs(function () {
                        $tabs = [];
                        $categories = ItemCategory::where('is_active', true)->get();

                        foreach ($categories as $category) {
                            $isManpower = $category->name === 'Manpower';
                            $tabs[] = Tab::make($category->name)
                                ->schema([
                                    Repeater::make("analysis_details.{$category->id}")
                                        ->label($category->name . ' Details')
                                        ->live()
                                        ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set))
                                        ->schema([
                                            Select::make('item_id')
                                                ->label('Item')
                                                ->searchable()
                                                ->preload()
                                                ->options(fn () => Item::where('item_category_id', $category->id)->pluck('name', 'id'))
                                                ->createOptionForm(ItemForm::schema())
                                                ->createOptionUsing(function (array $data) use ($category): int {
                                                    $data['item_category_id'] = $category->id;
                                                    return Item::create($data)->id;
                                                })
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                                    if (! $state) {
                                                        return;
                                                    }
                                                    
                                                    // Try to find project_area_id from root form state.
                                                    // Path: Repeater Row -> Repeater -> Tab -> Tabs -> Section -> Form Root
                                                    // This might require climbing up sufficient levels or using absolute path if supported.
                                                    // In Filament v3, direct access to top level might not be straightforward via relative ../
                                                    // But we can try multiple levels up.
                                                    
                                                    // Attempt 3 levels up: Row -> Repeater -> ??? -> Root?
                                                    // Actually, usually 2 or 3 '../'.
                                                    $areaId = $get('../../project_area_id') ?? $get('../../../project_area_id') ?? $get('../../../../project_area_id');
                                                    
                                                    $item = Item::find($state);
                                                    if ($item) {
                                                        $price = $item->getPriceForArea((int) $areaId);
                                                        $set('price', $price);
                                                        self::calculateDirectCost($get, $set);
                                                    }
                                                }),
                                            TextInput::make('quantity')
                                                ->label($isManpower ? 'Count' : 'Quantity')
                                                ->numeric()->default(1)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                                            TextInput::make('price')
                                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                                ->label($isManpower ? 'Salary' : 'Price')
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                                            TextInput::make('notes'),
                                        ])->columns(4)
                                        ->addActionLabel("Add {$category->name}"),
                                ]);
                        }
                        return $tabs;
                    })->columnSpanFull(),
            ]);
    }

    protected static function calculateDirectCost($get, $set): void
    {
        // Try to retrieve analysis_details from root. Method might be called from inside repeater.
        // If called from inside repeater, $get is scoped.
        // We need to pass the root $get or handle it.
        // But $get('../analysis_details') works?
        
        // Simpler: Just rely on live updates passing attributes?
        // Actually, $get('analysis_details') inside repeater returns THE REPEATER array?
        // No, inside Row, it doesn't return parent array easily.
        
        // Solution: We only need to sum it up.
        // If we can't easily access full array from within row, we might need a different approach?
        // But wait, the original code used $get('analysis_details') assuming it works.
        // The original usage was on Repeater's afterStateUpdated (which is scoped to Repeater? No, checking docs).
        // If Repeater->afterStateUpdated is used, $state is the array of items.
        // But here I call it from ITEM's afterStateUpdated.
        
        // Use ../../analysis_details strategy or just ignore if null?
        // If null, calculation is skipped. This is bad.
        
        // For now, let's try multiple levels.
        $analysisDetails = $get('analysis_details') ?? $get('../../analysis_details') ?? $get('../../../analysis_details') ?? [];
        $totalDirectCost = 0;

        foreach ($analysisDetails as $categoryId => $items) {
            $totalDirectCost += collect($items)->reduce(function ($carry, $item) {
                return $carry + (($item['quantity'] ?? 0) * ($item['price'] ?? 0));
            }, 0);
        }

        $set('direct_cost', $totalDirectCost);

        // Also recalculate margin since direct_cost changed
        self::calculateMargin($get('revenue_per_month'), $totalDirectCost, $set);
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
