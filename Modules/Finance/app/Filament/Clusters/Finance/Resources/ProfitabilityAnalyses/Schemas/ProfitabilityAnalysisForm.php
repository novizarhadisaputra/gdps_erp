<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Modules\CRM\Filament\Clusters\CRM\Resources\Customers\Schemas\CustomerForm;
use Modules\CRM\Models\CostingTemplate;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\Finance\Enums\AssetOwnership;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Services\ManpowerCostingService;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\Schemas\DirectCostCategoryForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\Schemas\ItemForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Schemas\JobPositionForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Schemas\ProductClusterForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Models\DirectCostCategory;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\TaxPtkpConfig;
use Modules\MasterData\Models\UnitOfMeasure;

class ProfitabilityAnalysisForm
{
    protected static array $modelCache = [];

    protected static function getCachedModel(string $modelClass, mixed $id): ?object
    {
        if (! $id) {
            return null;
        }
        $cacheKey = "{$modelClass}-{$id}";
        if (! isset(self::$modelCache[$cacheKey])) {
            self::$modelCache[$cacheKey] = $modelClass::find($id);
        }

        return self::$modelCache[$cacheKey];
    }

    protected static function parseNumericValue(mixed $value): float
    {
        if (empty($value)) {
            return 0.0;
        }

        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        // Convert to string and sanitize
        $value = (string) $value;

        // Remove any IDR / Currency symbols and spaces
        $value = preg_replace('/[^\d\.,-]/', '', $value);

        // Detect format: If multiple dots exist, period is thousand separator (ID)
        // If comma exists and period exists, and comma is later, period is thousand
        $dots = substr_count($value, '.');
        $commas = substr_count($value, ',');

        if ($dots > 1 || ($dots === 1 && $commas === 1 && strpos($value, '.') < strpos($value, ','))) {
            // ID Format: 1.000.000,50
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif ($dots === 1 && $commas === 0) {
            // Ambiguous Case: 1.000 or 1.23
            // In ID financial context, we usually use thousand separator with 3 digits
            $afterDot = substr($value, strpos($value, '.') + 1);
            if (strlen($afterDot) === 3) {
                // Likely thousand: 1.000
                $value = str_replace('.', '', $value);
            }
            // If not 3 digits, we leave it as decimal (e.g. 1.23 or 1.5)
        } elseif ($commas > 0) {
            // US/Mixed Format: 1,000,000.50 or 1,000.00 or 1,5
            // If only commas, it's likely ID decimal or US thousand.
            // But if it's followed by 2 digits, it's likely decimal in ID.
            if ($commas === 1 && $dots === 0) {
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        }

        return (float) $value;
    }

    public static function configure(Schema $schema, int|Closure $startStep = 1): Schema
    {
        return $schema->components(self::schema($startStep));
    }

    public static function schema(int|Closure $startStep = 1): array
    {
        return [
            Hidden::make('depreciation')->dehydrated(),
            Hidden::make('management_fee')->dehydrated(),

            Wizard::make([
                Step::make('Project Identification')
                    ->label('Project Identification')
                    ->description('Identify RR submission and associated customer.')
                    ->icon(Heroicon::Identification)
                    ->disabled(fn ($record) => $record && ! in_array($record->status?->value ?? $record->status, [ProfitabilityAnalysisStatus::Draft->value, ProfitabilityAnalysisStatus::Rejected->value]))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('general_information_id')
                                    ->relationship('generalInformation', 'number')
                                    ->label('GI Form')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->placeholder('Select GI Form / RR Submission')
                                    ->helperText('Select the General Information (RR) submission as the PA data basis.')
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (! $state) {
                                            return;
                                        }
                                        $gi = GeneralInformation::with(['lead.salesPlan', 'salesPlan'])->find($state);
                                        if (! $gi) {
                                            return;
                                        }

                                        $salesPlan = $gi->salesPlan ?? $gi->lead?->salesPlan;
                                        $lead = $gi->lead;

                                        $set('lead_id', $gi->lead_id);
                                        $set('customer_id', $gi->customer_id ?? $lead?->customer_id);
                                        $set('project_area_id', $gi->project_area_id ?? $salesPlan?->project_area_id);
                                        $set('product_cluster_id', $salesPlan?->product_cluster_id ?? $lead?->product_cluster_id);
                                        $set('tax_id', $gi->tax_id ?? $lead?->tax_id);
                                        $set('work_scheme_id', $gi->work_scheme_id);
                                        $set('project_type_id', $gi->project_type_id ?? $salesPlan?->project_type_id ?? $lead?->project_type_id);

                                        if ($gi->estimated_start_date) {
                                            $set('start_date', $gi->estimated_start_date->format('Y-m-d'));
                                            $set('year', $gi->estimated_start_date->year);
                                        }

                                        if ($gi->estimated_end_date) {
                                            $set('end_date', $gi->estimated_end_date->format('Y-m-d'));
                                        }

                                        if ($salesPlan) {
                                            $set('management_fee_rate', $salesPlan->management_fee_percentage);
                                            $set('payment_term_id', $salesPlan->payment_term_id);

                                            if (! empty($salesPlan->job_positions)) {
                                                $manpowerCategoryId = DirectCostCategory::where('code', 'manpower')->first()?->id;
                                                $duration = self::getProjectDurationMonths($get);

                                                $manpowerItems = collect($salesPlan->job_positions)->map(fn ($jobPositionId) => [
                                                    'costable_type' => JobPosition::class,
                                                    'costable_id' => $jobPositionId,
                                                    'direct_cost_category_id' => $manpowerCategoryId,
                                                    'unit_of_measure' => 'Person',
                                                    'quantity' => 1,
                                                    'duration_months' => $duration,
                                                    'markup_percentage' => 0,
                                                    'is_manpower' => true,
                                                ])->toArray();

                                                $set('manpowerItems', $manpowerItems);
                                            }

                                            // Recalculate if we have significant financial data
                                            self::calculateDirectCost($get, $set);
                                        }
                                    })
                                    ->dehydrated()
                                    ->columnSpan(1),
                                Select::make('customer_id')
                                    ->relationship('customer', 'name')
                                    ->label('Customer')
                                    ->helperText('Customer associated with the project.')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->placeholder('Select customer')
                                    ->helperText('The customer or employer entity.')
                                    ->columnSpan(1)
                                    ->createOptionForm(CustomerForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->editOptionForm(CustomerForm::schema())
                                    ->editOptionAction(fn (Action $action) => $action->slideOver())
                                    ->dehydrated(),
                                TextInput::make('document_number')
                                    ->label('Document Number')
                                    ->disabled()
                                    ->hiddenOn(operations: ['create'])
                                    ->dehydrated()
                                    ->placeholder('Auto-generated')
                                    ->columnSpan(1),
                                Hidden::make('lead_id'),
                                TextInput::make('revision_number')
                                    ->label('Revision #')
                                    ->disabled()
                                    ->default(0)
                                    ->hiddenOn(operations: ['create'])
                                    ->dehydrated()
                                    ->columnSpan(1),
                                TextInput::make('previous_code')
                                    ->label('Previous Code')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1)
                                    ->visible(fn ($record) => filled($record?->previous_code)),
                            ]),

                    ]),

                Step::make('Parameters & Assets')
                    ->label('Operational Parameters')
                    ->description('Configure project scope, work scheme, area, and asset ownership.')
                    ->icon(Heroicon::AdjustmentsHorizontal)
                    ->disabled(fn ($record) => $record && ! in_array($record->status?->value ?? $record->status, [ProfitabilityAnalysisStatus::Draft->value, ProfitabilityAnalysisStatus::Rejected->value]))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('product_cluster_id')
                                    ->relationship('productCluster', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->dehydrated()
                                    ->placeholder('Select product cluster')
                                    ->helperText('Categorization of the main project services.')
                                    ->default(fn ($livewire) => $livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->product_cluster_id : null)
                                    ->createOptionForm(ProductClusterForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver()),
                                Select::make('work_scheme_id')
                                    ->relationship('workScheme', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->placeholder('Select work scheme')
                                    ->helperText('Working pattern (affects manpower costing).')
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set))
                                    ->dehydrated(),
                                Select::make('project_area_id')
                                    ->relationship('projectArea', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->placeholder('Select project area')
                                    ->helperText('Main project location (affects minimum wage references).')
                                    ->default(fn ($livewire) => $livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->project_area_id : null)
                                    ->createOptionForm(ProjectAreaForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->dehydrated(),
                                TextInput::make('year')
                                    ->label('Year')
                                    ->numeric()
                                    ->required()
                                    ->default(now()->year)
                                    ->placeholder(now()->year)
                                    ->helperText('Budget year for minimum wage references.')
                                    ->live(onBlur: true)
                                    ->dehydrated(),
                                Select::make('project_type_id')
                                    ->relationship('projectType', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->dehydrated()
                                    ->placeholder('Select project type')
                                    ->helperText('Main project execution type (e.g. TAD, Borongan).')
                                    ->default(fn ($livewire) => $livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->project_type_id : null),

                                DatePicker::make('start_date')
                                    ->label('Start Date')
                                    ->required()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set))
                                    ->dehydrated(),
                                DatePicker::make('end_date')
                                    ->label('End Date')
                                    ->required()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set))
                                    ->dehydrated(),

                                Select::make('tax_id')
                                    ->label('Tax')
                                    ->relationship('tax', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select tax configuration')
                                    ->helperText('Tax configuration for the project code (e.g. PPN 11%).')
                                    ->dehydrated(),

                                Select::make('asset_ownership')
                                    ->options(AssetOwnership::class)
                                    ->default(AssetOwnership::GdpsOwned)
                                    ->required()
                                    ->placeholder('Select asset ownership')
                                    ->helperText('Determines the asset depreciation calculation model.')
                                    ->native(false)
                                    ->dehydrated(),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Toggle::make('require_manpower_costing')
                                    ->label('Require Manpower Costing')
                                    ->default(true)
                                    ->hidden(fn (Get $get) => (bool) $get('is_manual_cost'))
                                    ->live()
                                    ->dehydrated(),
                                Toggle::make('require_operational_costing')
                                    ->label('Require Operational Costing')
                                    ->default(true)
                                    ->hidden(fn (Get $get) => (bool) $get('is_manual_cost'))
                                    ->live()
                                    ->dehydrated(),
                                Toggle::make('is_manual_cost')
                                    ->label('Manual Cost Entry')
                                    ->default(false)
                                    ->helperText('Skip detail costing and enter totals manually.')
                                    ->live()
                                    ->dehydrated()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if ($state) {
                                            $set('require_manpower_costing', false);
                                            $set('require_operational_costing', false);
                                        } else {
                                            // Auto-enable detail costing when manual mode is turned off
                                            $set('require_manpower_costing', true);
                                            $set('require_operational_costing', true);
                                        }
                                        self::calculateDirectCost($get, $set);
                                    }),
                            ])->columnSpanFull(),
                    ]),

                Step::make('Financial Assumptions')
                    ->label('Financial Assumptions')
                    ->description('Set expectations for overhead costs, interest, and company tax.')
                    ->icon(Heroicon::Banknotes)
                    ->disabled(fn ($record) => $record && ! in_array($record->status?->value ?? $record->status, [ProfitabilityAnalysisStatus::Draft->value, ProfitabilityAnalysisStatus::Rejected->value]))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('interest_rate')
                                    ->label('Interest Rate (%)')
                                    ->numeric()
                                    ->default(1.50)
                                    ->placeholder('1.50')
                                    ->helperText('Estimated interest expense or cost of capital (Cost of Money).')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set))
                                    ->dehydrated(),
                                TextInput::make('tax_rate')
                                    ->label('Corp Tax Rate (%)')
                                    ->numeric()
                                    ->default(22.00)
                                    ->placeholder('22.00')
                                    ->helperText('Corporate Income Tax Rate.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set))
                                    ->dehydrated(),
                                TextInput::make('management_fee_rate')
                                    ->label('Mgmt Fee / Target GPM (%)')
                                    ->numeric()
                                    ->default(fn (Get $get, $livewire) => $get('/management_fee_rate') ?? ($livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->lead?->salesPlan?->management_fee_percentage : 0) ?? 15.00)
                                    ->placeholder('15.00')
                                    ->helperText('Target Gross Profit Margin percentage (Project Fee).')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set))
                                    ->dehydrated(),
                                Select::make('payment_term_id')
                                    ->relationship('paymentTerm', 'name')
                                    ->label('Payment Term (TOP)')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->default(fn (Get $get, $livewire) => $get('/payment_term_id') ?? ($livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->lead?->salesPlan?->payment_term_id : null))
                                    ->live(onBlur: true)
                                    ->placeholder('Select payment term')
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set))
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->dehydrated(),
                            ]),
                    ]),

                Step::make('Manpower Requirements')
                    ->label('Manpower Planning')
                    ->description('Determine personnel needs based on job positions or manpower packets.')
                    ->icon(Heroicon::UserGroup)
                    ->disabled(fn ($record) => $record && ! in_array($record->status?->value ?? $record->status, [ProfitabilityAnalysisStatus::Draft->value, ProfitabilityAnalysisStatus::Rejected->value]))
                    ->visible(fn (Get $get) => (bool) $get('is_manual_cost') || (bool) $get('require_manpower_costing'))
                    ->schema([
                        Select::make('analysis_details.manpower_template_id')
                            ->label('Manpower Template')
                            ->options(function (Get $get) {
                                $leadId = $get('lead_id');
                                if (! $leadId) {
                                    return [];
                                }

                                return ManpowerTemplate::query()
                                    ->where('lead_id', $leadId)
                                    ->get()
                                    ->mapWithKeys(fn ($item) => [$item->id => ($item->code ? "[{$item->code}] " : '').$item->name])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::handleManpowerTemplateSelection($state, $set, $get)),

                        TextEntry::make('manpower_link')
                            ->label('Manpower Costing Attachment')
                            ->state(fn (Get $get) => new HtmlString(self::getTemplateAttachmentLinkHtml($get('analysis_details.manpower_template_id'), 'manpower')))
                            ->html()
                            ->visible(fn (Get $get) => (bool) $get('is_manual_cost') && filled($get('analysis_details.manpower_template_id'))),

                        TextEntry::make('manpower_preview')
                            ->label('Personnel Summary')
                            ->state(fn (Get $get) => new HtmlString(self::getManpowerPreviewHtml($get('analysis_details.manpower_template_id'))))
                            ->html()
                            ->visible(fn (Get $get) => ! (bool) $get('is_manual_cost') && filled($get('analysis_details.manpower_template_id'))),

                        Repeater::make('manpowerItems')
                            ->schema([
                                TextInput::make('job_position'),
                                TextInput::make('quantity'),
                                TextInput::make('total_monthly_cost'),
                            ])
                            ->hidden()
                            ->dehydrated(),
                    ]),

                Step::make('Operational & Equipment Costs')

                    ->label('Operational & Equipment Costs')
                    ->description('Determine material, equipment, services, and other cost requirements.')
                    ->icon(Heroicon::ShoppingCart)
                    ->disabled(fn ($record) => $record && ! in_array($record->status?->value ?? $record->status, [ProfitabilityAnalysisStatus::Draft->value, ProfitabilityAnalysisStatus::Rejected->value]))
                    ->visible(fn (Get $get) => (bool) $get('is_manual_cost') || (bool) $get('require_operational_costing'))
                    ->schema([
                        Select::make('analysis_details.costing_template_id')
                            ->label('Operational Template')
                            ->options(function (Get $get) {
                                $leadId = $get('lead_id');
                                if (! $leadId) {
                                    return [];
                                }

                                return CostingTemplate::query()
                                    ->where('lead_id', $leadId)
                                    ->get()
                                    ->mapWithKeys(fn ($item) => [$item->id => ($item->code ? "[{$item->code}] " : '').$item->name])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::handleCostingTemplateSelection($state, $set, $get)),

                        TextEntry::make('operational_link')
                            ->label('Operational Costing Attachment')
                            ->state(fn (Get $get) => new HtmlString(self::getTemplateAttachmentLinkHtml($get('analysis_details.costing_template_id'), 'costing')))
                            ->html()
                            ->visible(fn (Get $get) => (bool) $get('is_manual_cost') && filled($get('analysis_details.costing_template_id'))),

                        TextEntry::make('operational_preview')
                            ->label('Equipment & Material Summary')
                            ->state(fn (Get $get) => new HtmlString(self::getOperationalPreviewHtml($get('analysis_details.costing_template_id'))))
                            ->html()
                            ->visible(fn (Get $get) => ! (bool) $get('is_manual_cost') && filled($get('analysis_details.costing_template_id'))),
                    ]),

                Step::make('Manual Costing')

                    ->label('Manual Cost Entry')
                    ->description('Enter high-level monthly direct costs and revenue.')
                    ->icon(Heroicon::Calculator)
                    ->visible(fn (Get $get) => (bool) $get('is_manual_cost') || ! empty($get('analysis_details.manual_costs')))
                    ->disabled(fn ($record) => $record && ! in_array($record->status?->value ?? $record->status, [ProfitabilityAnalysisStatus::Draft->value, ProfitabilityAnalysisStatus::Rejected->value]))
                    ->schema([
                        Section::make('Monthly Budgeting')
                            ->description('Provide estimated monthly totals for direct cost categories.')
                            ->schema([
                                TextInput::make('analysis_details.manual_revenue')
                                    ->label('Total Direct Cost Breakdown (Subtotal)')
                                    ->numeric()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR ')
                                    ->readOnly()
                                    ->helperText('Otomatis menghitung jumlah dari breakdown biaya di bawah.')
                                    ->columnSpan(2)
                                    ->dehydrated(),
                                TextInput::make('manual_depreciation')
                                    ->label('Manual Depreciation')
                                    ->numeric()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR ')
                                    ->default(0)
                                    ->helperText('Enter monthly depreciation amount manually.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set))
                                    ->columnSpan(1)
                                    ->dehydrated(),

                                TextEntry::make('lead_documents')
                                    ->label('Existing Lead/GI Documents')
                                    ->columnSpanFull()
                                    ->state(function ($get, $record) {
                                        $leadId = $get('lead_id') ?? $record?->lead_id;
                                        if (! $leadId) {
                                            return 'Please select a Lead first.';
                                        }

                                        $lead = Lead::find($leadId);
                                        if (! $lead) {
                                            return 'Lead tidak ditemukan.';
                                        }

                                        $links = collect();

                                        // Standard Heroicons SVGs for consistent rendering in HtmlString
                                        $docIcon = '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5H5.625Z" /><path d="M12.971 1.816A5.23 5.23 0 0 1 14.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 0 1 3.434 1.279 9.768 9.768 0 0 0-6.963-6.963Z" /></svg>';
                                        $dupIcon = '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M7.5 3.375c0-1.036.84-1.875 1.875-1.875h.375a3.75 3.75 0 0 1 3.75 3.75v1.875C13.5 8.161 14.34 9 15.375 9h1.875A3.75 3.75 0 0 1 21 12.75v3.375C21 17.16 20.16 18 19.125 18h-9.75A1.875 1.875 0 0 1 7.5 16.125V3.375Z" /><path d="M15 5.25a5.23 5.23 0 0 0-1.279-3.434 9.768 9.768 0 0 1 6.963 6.963A5.23 5.23 0 0 0 17.25 7.5h-1.875A.375.375 0 0 1 15 7.125V5.25ZM4.875 6c-1.036 0-1.875.84-1.875 1.875v12.75c0 1.035.84 1.875 1.875 1.875h9.75c1.035 0 1.875-.84 1.875-1.875V17.25a.75.75 0 0 0-1.5 0v2.25c0 .207-.168.375-.375.375h-9.75a.375.375 0 0 1-.375-.375V7.875c0-.207.168-.375.375-.375H7.5a.75.75 0 0 0 0-1.5H4.875Z" /></svg>';

                                        // Get Lead Media (RFQ, RFP, etc)
                                        $lead->getMedia('*')->each(function ($media) use ($links, $docIcon) {
                                            $url = $media->disk === 's3'
                                                ? $media->getTemporaryUrl(now()->addMinutes(30))
                                                : $media->getUrl();

                                            $links->push(sprintf(
                                                '<a href="%s" target="_blank" class="text-primary-600 hover:text-primary-500 transition-colors flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                                    %s
                                                    <span class="truncate">%s</span>
                                                    <span class="text-[10px] uppercase font-bold text-gray-400 bg-white dark:bg-gray-900 px-1.5 py-0.5 rounded border border-gray-200 dark:border-gray-700 ml-auto">%s</span>
                                                </a>',
                                                $url,
                                                $docIcon,
                                                $media->file_name,
                                                $media->collection_name
                                            ));
                                        });

                                        // Get GI Media
                                        $lead->generalInformations->each(function ($gi) use ($links, $dupIcon) {
                                            $gi->getMedia('*')->each(function ($media) use ($links, $dupIcon) {
                                                $url = $media->disk === 's3'
                                                    ? $media->getTemporaryUrl(now()->addMinutes(30))
                                                    : $media->getUrl();

                                                $links->push(sprintf(
                                                    '<a href="%s" target="_blank" class="text-primary-600 hover:text-primary-500 transition-colors flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                                        %s
                                                        <span class="truncate">%s</span>
                                                        <span class="text-[10px] uppercase font-bold text-blue-400 bg-white dark:bg-gray-900 px-1.5 py-0.5 rounded border border-blue-100 dark:border-blue-900/50 ml-auto">%s</span>
                                                    </a>',
                                                    $url,
                                                    $dupIcon,
                                                    $media->file_name,
                                                    'GI - '.$media->collection_name
                                                ));
                                            });
                                        });

                                        if ($links->isEmpty()) {
                                            return new HtmlString('<div class="text-sm text-gray-500 italic p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-dashed border-gray-200 dark:border-gray-700">Tidak ada dokumen yang ditemukan di Lead/GI.</div>');
                                        }

                                        return new HtmlString('<div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">'.$links->implode('').'</div>');
                                    })
                                    ->html(),
                                Repeater::make('analysis_details.manual_costs')
                                    ->label('Manual Cost Breakdown')
                                    ->itemLabel(fn (array $state): ?string => DirectCostCategory::find($state['direct_cost_category_id'] ?? null)?->name ?? 'New Manual Cost')
                                    ->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set))
                                    ->afterStateHydrated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set))
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Select::make('direct_cost_category_id')
                                                    ->label('Category')
                                                    ->options(fn () => DirectCostCategory::where('type', 'direct')->whereNull('parent_id')->pluck('name', 'id'))
                                                    ->required()
                                                    ->distinct()
                                                    ->live()
                                                    ->createOptionForm(DirectCostCategoryForm::schema(type: 'direct'))
                                                    ->createOptionUsing(fn (array $data) => DirectCostCategory::create($data)->id)
                                                    ->editOptionForm(DirectCostCategoryForm::schema(type: 'direct'))
                                                    ->fillEditOptionActionFormUsing(fn (Select $component): ?array => DirectCostCategory::find($component->getState())?->toArray())
                                                    ->updateOptionUsing(fn (Select $component, array $data) => DirectCostCategory::find($component->getState())?->update($data))
                                                    ->columnSpan(2),
                                                TextInput::make('amount')
                                                    ->label('Category Total')
                                                    ->numeric()
                                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                    ->prefix('IDR ')
                                                    ->required()
                                                    ->placeholder('Enter total or add breakdown below')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set, '../../'))
                                                    ->extraAttributes(['class' => 'font-bold bg-gray-50'])
                                                    ->columnSpan(1),
                                            ]),
                                        TextInput::make('description')
                                            ->label('Description/Notes'),
                                        Repeater::make('sub_items')
                                            ->label('Sub-component Breakdown')
                                            ->schema([
                                                Grid::make(4)
                                                    ->schema([
                                                        Select::make('job_position_id')
                                                            ->label('Master Job Position')
                                                            ->options(JobPosition::pluck('name', 'id'))
                                                            ->searchable()
                                                            ->preload()
                                                            ->live()
                                                            ->visible(fn (Get $get) => DirectCostCategory::find($get('../../direct_cost_category_id'))?->code === 'manpower')
                                                            ->createOptionForm(JobPositionForm::schema())
                                                            ->createOptionAction(fn (Action $action) => $action->slideOver())
                                                            ->createOptionUsing(fn (array $data) => JobPosition::create($data)->id)
                                                            ->afterStateUpdated(function ($state, Set $set) {
                                                                if ($state) {
                                                                    $set('name', JobPosition::find($state)?->name);
                                                                    $set('unit_of_measure', 'Person');
                                                                }
                                                            })
                                                            ->columnSpan(2),

                                                        Select::make('item_id')
                                                            ->label('Master Item')
                                                            ->options(Item::pluck('name', 'id'))
                                                            ->searchable()
                                                            ->preload()
                                                            ->live()
                                                            ->visible(fn (Get $get) => DirectCostCategory::find($get('../../direct_cost_category_id'))?->code === 'tools_equipment')
                                                            ->createOptionForm(ItemForm::schema())
                                                            ->createOptionAction(fn (Action $action) => $action->slideOver())
                                                            ->createOptionUsing(fn (array $data) => Item::create($data)->id)
                                                            ->afterStateUpdated(function ($state, Set $set) {
                                                                if ($state) {
                                                                    $item = Item::with('unitOfMeasure')->find($state);
                                                                    $set('name', $item?->name);
                                                                    $set('unit_of_measure', $item?->unitOfMeasure?->name);
                                                                    $set('unit_amount', $item?->price);
                                                                }
                                                            })
                                                            ->columnSpan(2),

                                                        TextInput::make('name')
                                                            ->label('Sub-item Name')
                                                            ->required()
                                                            ->placeholder('e.g. Salary')
                                                            ->columnSpan(2),
                                                        TextInput::make('quantity')
                                                            ->label('Qty')
                                                            ->numeric()
                                                            ->default(1)
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateSubItemAmount($get, $set))
                                                            ->columnSpan(1),
                                                        Select::make('unit_of_measure')
                                                            ->label('UoM')
                                                            ->options(UnitOfMeasure::pluck('name', 'name'))
                                                            ->searchable()
                                                            ->preload()
                                                            ->placeholder('Org, Pcs')
                                                            ->columnSpan(1),
                                                        TextInput::make('unit_amount')
                                                            ->label('Monthly Unit Cost')
                                                            ->numeric()
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                                            ->prefix('IDR ')
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateSubItemAmount($get, $set))
                                                            ->columnSpan(1),
                                                        TextInput::make('amount')
                                                            ->label('Total')
                                                            ->numeric()
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                            ->prefix('IDR ')
                                                            ->readOnly()
                                                            ->required()
                                                            ->columnSpan(1),

                                                    ]),
                                            ])
                                            ->collapsible()
                                            ->defaultItems(0)
                                            ->reorderableWithButtons()
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateIndirectCost($get, $set)),
                                    ])
                                    ->columnSpanFull()
                                    ->itemLabel(fn (array $state): ?string => filled($state['direct_cost_category_id'] ?? null) ? DirectCostCategory::find($state['direct_cost_category_id'])?->name : 'New Manual Cost')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set)),
                            ]),
                    ]),

                Step::make('Indirect Costs')
                    ->label('Indirect Costs')
                    ->description('Set management expenses, entertainment, and other indirect fees.')
                    ->icon(Heroicon::ReceiptPercent)
                    ->disabled(fn ($record) => $record && ! in_array($record->status?->value ?? $record->status, [ProfitabilityAnalysisStatus::Draft->value, ProfitabilityAnalysisStatus::Rejected->value]))
                    ->schema([

                        TextInput::make('analysis_details.manual_indirect_total')
                            ->label('Total Indirect Cost (Subtotal)')
                            ->numeric()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('IDR ')
                            ->readOnly()
                            ->helperText('Otomatis menghitung jumlah dari rincian biaya tidak langsung di bawah.')
                            ->columnSpanFull()
                            ->dehydrated(),
                        Repeater::make('analysis_details.indirect_costs')
                            ->label('Indirect Cost Items')
                            ->dehydrated()
                            ->schema([
                                Grid::make(3)
                                    ->schema([

                                        Select::make('direct_cost_category_id')
                                            ->label('Category')
                                            ->options(fn () => DirectCostCategory::where('type', 'indirect')->whereNull('parent_id')->pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->createOptionForm(DirectCostCategoryForm::schema(type: 'indirect'))
                                            ->createOptionUsing(fn (array $data) => DirectCostCategory::create($data)->id)
                                            ->editOptionForm(DirectCostCategoryForm::schema(type: 'indirect'))
                                            ->fillEditOptionActionFormUsing(fn (Select $component): ?array => DirectCostCategory::find($component->getState())?->toArray())
                                            ->updateOptionUsing(fn (Select $component, array $data) => DirectCostCategory::find($component->getState())?->update($data)),
                                        Select::make('calculation_type')
                                            ->label('Calculation Type')
                                            ->options([
                                                'nominal' => 'Nominal',
                                                'percentage' => 'Percentage',
                                            ])
                                            ->required()
                                            ->default('nominal')
                                            ->live()
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set, '../../')),
                                        Select::make('percentage_basis')
                                            ->label('Basis')
                                            ->options([
                                                'revenue' => 'Total Revenue',
                                                'direct_cost' => 'Total Direct Cost',
                                            ])
                                            ->required(fn (Get $get) => $get('calculation_type') === 'percentage')
                                            ->visible(fn (Get $get) => $get('calculation_type') === 'percentage')
                                            ->default('revenue')
                                            ->live()
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateIndirectCost($get, $set, '../../')),
                                        TextInput::make('unit_cost_price')
                                            ->label(fn (Get $get) => $get('calculation_type') === 'percentage' ? 'Percentage (%)' : 'Category Total')
                                            ->numeric()
                                            ->currencyMask(
                                                thousandSeparator: '.',
                                                decimalSeparator: ',',
                                                precision: 2
                                            )
                                            ->prefix(fn (Get $get) => $get('calculation_type') === 'percentage' ? null : 'IDR ')
                                            ->suffix(fn (Get $get) => $get('calculation_type') === 'percentage' ? '%' : null)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateIndirectCost($get, $set, '../../')),
                                        TextInput::make('description')
                                            ->label('Description/Notes')
                                            ->placeholder('Optional notes for this indirect category')
                                            ->columnSpanFull(),
                                        Repeater::make('sub_items')
                                            ->label('Sub-component Breakdown')
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        TextInput::make('name')
                                                            ->label('Sub-item Name')
                                                            ->required()
                                                            ->placeholder('e.g. Office Rent')
                                                            ->columnSpan(2),
                                                        TextInput::make('quantity')
                                                            ->label('Qty')
                                                            ->numeric()
                                                            ->default(1)
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateSubItemAmountForIndirect($get, $set))
                                                            ->columnSpan(1),
                                                        Select::make('unit_of_measure')
                                                            ->label('UoM')
                                                            ->options(UnitOfMeasure::pluck('name', 'name'))
                                                            ->searchable()
                                                            ->preload()
                                                            ->placeholder('Mo, Unit')
                                                            ->columnSpan(1),
                                                        TextInput::make('unit_amount')
                                                            ->label('Unit Price')
                                                            ->numeric()
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                            ->prefix('IDR ')
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateSubItemAmountForIndirect($get, $set))
                                                            ->columnSpan(1),
                                                        TextInput::make('amount')
                                                            ->label('Total')
                                                            ->numeric()
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                            ->prefix('IDR ')
                                                            ->readOnly()
                                                            ->required()
                                                            ->columnSpan(1),
                                                    ]),
                                            ])
                                            ->collapsible()
                                            ->defaultItems(0)
                                            ->reorderableWithButtons()
                                            ->visible(fn (Get $get) => $get('calculation_type') === 'nominal')
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateIndirectCost($get, $set)),
                                    ]),
                            ])
                            ->itemLabel(fn (array $state): ?string => DirectCostCategory::find($state['direct_cost_category_id'] ?? null)?->name ?? 'New Indirect Cost')
                            ->columnSpanFull()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateIndirectCost($get, $set)),
                    ]),

                Step::make('Financial Performance')

                    ->label('Financial Performance')
                    ->description('Key monthly and project-wide financial metrics.')
                    ->icon(Heroicon::PresentationChartLine)
                    ->disabled(fn ($record) => $record && ! in_array($record->status?->value ?? $record->status, [ProfitabilityAnalysisStatus::Draft->value, ProfitabilityAnalysisStatus::Rejected->value]))
                    ->schema([
                        Section::make('Automated Cost Review')
                            ->description('Aggregated monthly costs calculated from personnel, tools, and indirect inputs.')
                            ->visible(fn (Get $get) => ! (bool) $get('is_manual_cost'))
                            ->icon(Heroicon::MagnifyingGlassCircle)
                            ->collapsible()
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('direct_cost_manpower')
                                            ->label('Personnel (Monthly)')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->helperText('Otomatis dari tab Manpower.'),
                                        TextInput::make('direct_cost_tools')
                                            ->label('Tools & Eq (Monthly)')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->helperText('Otomatis dari tab Operational.'),
                                        TextInput::make('direct_cost_material')
                                            ->label('Materials (Monthly)')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->helperText('Otomatis dari tab Operational.'),
                                        TextInput::make('avg_monthly_indirect_cost')
                                            ->label('Indirect (Monthly)')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated(false)
                                            ->helperText('Otomatis dari tab Indirect.'),
                                    ]),
                            ]),

                        Section::make('Monthly Performance Metrics')
                            ->description('Monthly average revenue, cost, and profit.')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('revenue_per_month')
                                            ->label('MONTHLY REVENUE')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'text-2xl font-black text-success-600']),
                                        TextInput::make('direct_cost')
                                            ->label('MONTHLY DIRECT COST')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'text-2xl font-black text-danger-600']),
                                        TextInput::make('gross_profit')
                                            ->label('MONTHLY GROSS PROFIT')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'text-2xl font-black text-primary-600']),
                                    ]),

                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('ebitda')
                                            ->label('EBITDA')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold']),
                                        TextInput::make('ebit')
                                            ->label('EBIT')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold']),
                                        TextInput::make('ebt')
                                            ->label('EBT')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold']),
                                        TextInput::make('net_profit')
                                            ->label('NET PROFIT (MONTHLY)')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold text-success-700']),
                                        TextInput::make('net_profit_margin')
                                            ->label('NPM (%)')
                                            ->numeric()
                                            ->suffix('%')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold text-primary-700']),
                                    ])->extraAttributes(['class' => 'border-t border-gray-100 pt-4 mt-4']),
                            ]),

                        Section::make('Cost Breakdown Details')
                            ->collapsed()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('direct_cost_manpower')
                                            ->label('Personnel')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated(),
                                        TextInput::make('direct_cost_tools')
                                            ->label('Tools & Eq')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated(),
                                        TextInput::make('direct_cost_material')
                                            ->label('Materials')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated(),
                                    ]),
                            ]),

                        Section::make('Total Project Value (Full Duration)')
                            ->compact()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('total_project_revenue')
                                            ->label('PROJECT REVENUE')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold text-success-600']),
                                        TextInput::make('total_project_cost')
                                            ->label('PROJECT COST')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold text-danger-600']),
                                        TextInput::make('margin_percentage')
                                            ->label('GPM (%)')
                                            ->suffix('%')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold text-primary-600']),
                                    ]),
                            ]),
                    ]),
            ])
                ->columnSpanFull()
                ->startOnStep($startStep)
                ->persistStepInQueryString(),
        ];
    }

    public static function getProjectDurationMonths($get, string $root = ''): float
    {
        $startDate = $get($root.'start_date') ?? $get('/start_date');
        $endDate = $get($root.'end_date') ?? $get('/end_date');

        if ($startDate && $endDate) {
            $start = \Carbon\Carbon::parse($startDate);
            $end = \Carbon\Carbon::parse($endDate);
            $days = $start->diffInDays($end);

            return max(1, round($days / 30, 2));
        }

        $giId = $get($root.'general_information_id') ?? $get('/general_information_id');
        $gi = self::getCachedModel(GeneralInformation::class, $giId);

        if ($gi && $gi->estimated_start_date && $gi->estimated_end_date) {
            $days = $gi->estimated_start_date->diffInDays($gi->estimated_end_date);

            return max(1, round($days / 30, 2));
        }

        return 1.0;
    }

    public static function calculateDirectCost($get, $set, string $root = ''): void
    {
        // 1. Calculate Project Duration
        $projectDurationMonths = self::getProjectDurationMonths($get, $root);

        $totalProjectCost = 0;
        $totalProjectRevenue = 0;
        $totalProjectDepreciation = 0;
        $totalProjectIndirectCost = 0;

        $manpowerCostMonthly = 0;
        $toolsCostMonthly = 0;
        $materialCostMonthly = 0;

        $isManual = (bool) ($get($root.'is_manual_cost') ?? $get('is_manual_cost') ?? $get('/is_manual_cost') ?? false);

        // 1. Calculate Manual Costs (Step 3) - ALWAYS DO THIS
        $totalStep3Cost = 0;
        $manualCosts = $get($root.'analysis_details.manual_costs') ?? $get('analysis_details.manual_costs') ?? $get('/analysis_details.manual_costs') ?? [];

        $catManpowerId = DirectCostCategory::where('code', 'manpower')->first()?->id;
        $catToolsId = DirectCostCategory::where('code', 'tools_equipment')->first()?->id;
        $catMaterialId = DirectCostCategory::where('code', 'material')->first()?->id;

        foreach ($manualCosts as $item) {
            // IGNORE items without a selected category to prevent garbage data
            if (empty($item['direct_cost_category_id'])) {
                continue;
            }

            $rawAmount = $item['amount'] ?? 0;
            $amount = self::parseNumericValue($rawAmount);

            $totalStep3Cost += $amount;

            if (isset($item['direct_cost_category_id'])) {
                if ((string) $item['direct_cost_category_id'] === (string) $catManpowerId) {
                    $manpowerCostMonthly += $amount;
                } elseif ((string) $item['direct_cost_category_id'] === (string) $catToolsId) {
                    $toolsCostMonthly += $amount;
                } elseif ((string) $item['direct_cost_category_id'] === (string) $catMaterialId) {
                    $materialCostMonthly += $amount;
                } else {
                    $materialCostMonthly += $amount;
                }
            }
        }

        // Update Monthly Direct Cost and UI Display
        $set($root.'direct_cost', $totalStep3Cost);
        $set($root.'analysis_details.manual_revenue', $totalStep3Cost);

        if ($isManual || ! empty($manualCosts)) {
            $totalProjectCost = $totalStep3Cost * $projectDurationMonths;

            $totalProjectDepreciation = self::parseNumericValue($get($root.'manual_depreciation') ?? $get('manual_depreciation') ?? $get('/manual_depreciation') ?? 0) * $projectDurationMonths;

            // These are already monthly totals from Step 3 summation above
            // No need to divide by duration again, they are already the monthly target.
        } else {
            // First pass: Calculate Revenue and Direct Costs from Fixed/Nominal items
            // (We need an initial revenue estimate for percentage-based costs)
            $manpowerItems = $get($root.'manpowerItems') ?? $get('manpowerItems') ?? $get('/manpowerItems') ?? [];
            $operationalItems = $get($root.'operationalItems') ?? $get('operationalItems') ?? $get('/operationalItems') ?? [];

            // To handle percentages correctly, we do it in a way that avoids circular dependency
            // Initial revenue is often set by the user or calculated from cost + markup.

            $tempTotalCost = 0;
            $tempTotalRevenue = 0;
            $tempTotalDepreciation = 0;

            // 1. Calculate Manpower and Nominal Operational Costs (First Pass)
            $allDirectItems = array_merge($manpowerItems, $operationalItems);

            foreach ($allDirectItems as $item) {
                if (($item['calculation_type'] ?? 'nominal') !== 'nominal') {
                    continue;
                }

                $itemGet = function ($path) use ($item, $get) {
                    if (str_starts_with($path, '/')) {
                        return $get(ltrim($path, '/'));
                    }

                    return data_get($item, $path);
                };

                $monthlyCost = self::calculateItemMonthlyCost($itemGet);
                $markup = (float) ($item['markup_percentage'] ?? 0);
                $duration = (float) ($item['duration_months'] ?? $projectDurationMonths);
                $monthlySale = $monthlyCost * (1.0 + ($markup / 100));

                $tempTotalCost += ($monthlyCost * $duration);
                $tempTotalRevenue += ($monthlySale * $duration);

                // Tracking for monthly breakdown view
                $catId = $item['direct_cost_category_id'] ?? null;
                $cat = $catId ? DirectCostCategory::find($catId) : null;
                $isManpowerAssumed = $item['is_manpower'] ?? false;

                if ($cat?->code === 'manpower' || $isManpowerAssumed) {
                    $manpowerCostMonthly += $monthlyCost;
                } elseif ($cat?->code === 'tools_equipment') {
                    $toolsCostMonthly += $monthlyCost;
                } elseif ($cat?->code === 'material') {
                    $materialCostMonthly += $monthlyCost;
                } else {
                    $materialCostMonthly += $monthlyCost;
                }

                // Track depreciation (Direct items only)
                if (! ($item['is_manpower'] ?? false)) {
                    $deprMonths = (float) ($item['depreciation_months'] ?? 1);
                    if ($deprMonths > 0) {
                        $costPrice = (float) ($item['unit_cost_price'] ?? 0);
                        $qty = (float) ($item['quantity'] ?? 1);
                        $monthlyDepreciation = ($costPrice / $deprMonths) * $qty;
                        $tempTotalDepreciation += ($monthlyDepreciation * $duration);
                    }
                }
            }

            // 2. Calculate Percentage-based Operational Costs (Second Pass)
            foreach ($allDirectItems as $item) {
                if (($item['calculation_type'] ?? 'nominal') === 'nominal') {
                    continue;
                }

                $itemGet = function ($path) use ($item, $get) {
                    if (str_starts_with($path, '/')) {
                        return $get(ltrim($path, '/'));
                    }

                    return data_get($item, $path);
                };

                // Use temp values as basis for accurate percentage calculation
                $currentAvgRevenue = $projectDurationMonths > 0 ? $tempTotalRevenue / $projectDurationMonths : 0;
                $currentAvgCost = $projectDurationMonths > 0 ? $tempTotalCost / $projectDurationMonths : 0;

                $monthlyCost = self::calculateItemMonthlyCost($itemGet, $currentAvgRevenue, $currentAvgCost);
                $markup = (float) ($item['markup_percentage'] ?? 0);
                $duration = (float) ($item['duration_months'] ?? $projectDurationMonths);
                $monthlySale = $monthlyCost * (1.0 + ($markup / 100));

                $tempTotalCost += ($monthlyCost * $duration);
                $tempTotalRevenue += ($monthlySale * $duration);

                // Add to breakdown trackers if needed
                $materialCostMonthly += $monthlyCost;
            }

            $totalProjectCost = $tempTotalCost;
            $totalProjectRevenue = $tempTotalRevenue;
            $totalProjectDepreciation = $tempTotalDepreciation;
        }

        // 2. Always Calculate Indirect Items (OPEX)
        $indirectItems = $get($root.'analysis_details.indirect_costs') ?? $get('analysis_details.indirect_costs') ?? $get('/analysis_details.indirect_costs') ?? [];
        $totalProjectIndirectCost = 0;
        foreach ($indirectItems as $item) {
            $itemGet = function ($path) use ($item, $get) {
                if (str_starts_with($path, '/')) {
                    return $get(ltrim($path, '/'));
                }

                return data_get($item, $path);
            };
            $monthlyCost = self::calculateItemMonthlyCost(
                $itemGet,
                $projectDurationMonths > 0 ? $totalProjectRevenue / $projectDurationMonths : 0,
                $projectDurationMonths > 0 ? $totalProjectCost / $projectDurationMonths : 0
            );
            $duration = (float) ($item['duration_months'] ?? $projectDurationMonths);
            $totalProjectIndirectCost += ($monthlyCost * $duration);
        }

        // Handle Management Fee from Rate
        $mgmtFeeRate = self::parseNumericValue($get($root.'management_fee_rate') ?? $get('management_fee_rate') ?? $get('/management_fee_rate') ?? 0);
        $avgMonthlyDirectCost = $projectDurationMonths > 0 ? ($totalProjectCost / $projectDurationMonths) : 0;

        if ($mgmtFeeRate > 0) {
            $calculatedMgmtFee = $avgMonthlyDirectCost * ($mgmtFeeRate / 100);
            $set($root.'management_fee', $calculatedMgmtFee);
            $mgmtFee = $calculatedMgmtFee;
        } else {
            $mgmtFee = (float) ($get($root.'management_fee') ?? $get('management_fee') ?? $get('/management_fee') ?? 0);
        }

        // Add Management Fee to Revenue (Pro-rated monthly)
        $totalProjectRevenue += ($mgmtFee * $projectDurationMonths);

        // 3. Finalize Monthly Direct Cost Tracers
        $avgMonthlyCost = $projectDurationMonths > 0 ? ($totalProjectCost / $projectDurationMonths) : $totalStep3Cost;
        $avgMonthlyDepreciation = $projectDurationMonths > 0 ? ($totalProjectDepreciation / $projectDurationMonths) : self::parseNumericValue($get($root.'manual_depreciation') ?? $get('manual_depreciation') ?? $get('/manual_depreciation') ?? 0);

        $set($root.'direct_cost', $avgMonthlyCost);
        $set($root.'analysis_details.manual_revenue', $totalStep3Cost); // Use raw sum for UI display in Step 5
        $set($root.'depreciation', $avgMonthlyDepreciation);
        $set($root.'direct_cost_manpower', $manpowerCostMonthly);
        $set($root.'direct_cost_tools', $toolsCostMonthly);
        $set($root.'direct_cost_material', $materialCostMonthly);
        $set($root.'total_project_cost_direct', $totalProjectCost);
        $set($root.'total_project_depreciation', $totalProjectDepreciation);

        // 4. Trigger Global Performance Recalculation
        self::calculatePerformance($get, $set, $root);
    }

    protected static function updateItemTotals($get, $set): void
    {
        $set('total_monthly_cost', self::calculateItemMonthlyCost($get));
        $set('total_monthly_sale', self::calculateItemMonthlySale($get));

        // Bubble up calculation to global totals
        self::calculateDirectCost($get, $set);
    }

    public static function calculateItemMonthlyCost($get, ?float $totalRevenue = null, ?float $totalDirectCost = null): float
    {
        $qty = self::parseNumericValue($get('quantity') ?? 1);
        $costPrice = self::parseNumericValue($get('unit_cost_price') ?? 0);
        $deprMonths = self::parseNumericValue($get('depreciation_months') ?? 1);
        $calcType = $get('calculation_type') ?? 'nominal';
        $basis = $get('percentage_basis') ?? 'none';

        if ($calcType === 'percentage') {
            $basisValue = 0;
            if ($basis === 'revenue') {
                $basisValue = $totalRevenue ?? self::parseNumericValue($get('/revenue_per_month') ?? 0);
            } elseif ($basis === 'direct_cost') {
                // Warning: Potential circular dependency if called during direct cost calculation
                $basisValue = $totalDirectCost ?? self::parseNumericValue($get('/direct_cost') ?? 0);
            }

            return ($basisValue * ($costPrice / 100)) * $qty;
        }

        $costBreakdown = $get('cost_breakdown') ?? [];

        $isManpower = $get('is_manpower');
        if (! $isManpower && $get('costable_type') && $get('costable_id')) {
            if ($get('costable_type') === Item::class) {
                $costableId = $get('costable_id');
                $dbItem = filled($costableId) ? Item::find($costableId) : null;
                $isManpower = $dbItem?->category?->name === 'Manpower';
            } elseif ($get('costable_type') === JobPosition::class) {
                $isManpower = true;
            } elseif ($get('costable_type') === ManpowerTemplate::class) {
                $isManpower = false; // ManpowerTemplate calculates its own total
            }
        }

        if ($isManpower) {
            $service = app(ManpowerCostingService::class);
            $result = $service->calculate(
                basicSalary: $costPrice,
                allowances: $costBreakdown,
                projectAreaId: (string) ($get('/project_area_id')),
                year: (int) ($get('/year') ?? date('Y')),
                riskLevel: $get('risk_level') ?? 'very_low',
                isLaborIntensive: (bool) $get('is_labor_intensive'),
                employeeType: $get('employee_type') ?? 'ppu',
                billThrMonthly: (bool) ($get('bill_thr_monthly') ?? true),
                billCompensationMonthly: (bool) ($get('bill_compensation_monthly') ?? true),
                includeNonFixedInAccruals: (bool) ($get('include_non_fixed_in_accruals') ?? false),
                extraCosts: $get('extra_costs') ?? [],
                ptkpCode: filled($get('ptkp_config_id')) ? TaxPtkpConfig::find($get('ptkp_config_id'))?->code ?? 'TK/0' : 'TK/0',
                isBpjsActive: (bool) ($get('is_bpjs_active') ?? true)
            );

            return (float) ($result['total_direct_cost'] ?? 0) * $qty;
        }

        if ($deprMonths <= 0) {
            $deprMonths = 1.0;
        }

        $addOnTotal = 0.0;
        foreach ($costBreakdown as $addon) {
            $val = 0.0;
            if (! empty($addon['details'])) {
                $val = collect($addon['details'])->sum(fn ($detail) => self::parseNumericValue($detail['value'] ?? 0));
            } else {
                $val = self::parseNumericValue($addon['value'] ?? 0);
            }

            $type = $addon['type'] ?? 'nominal';

            if ($type === 'percentage') {
                $addOnTotal += $costPrice * ($val / 100);
            } else {
                $addOnTotal += $val;
            }
        }

        return (($costPrice / $deprMonths) + $addOnTotal) * $qty;
    }

    public static function calculateItemMonthlySale($get): float
    {
        $monthlyCost = self::calculateItemMonthlyCost($get);
        $markup = self::parseNumericValue($get('markup_percentage') ?? 0);

        return $monthlyCost * (1.0 + ($markup / 100));
    }

    public static function handleManpowerTemplateSelection($state, Set $set, Get $get): void
    {
        if (! $state) {
            $set('manpowerItems', []);
            $set('analysis_details.manpower_template_id', null);

            return;
        }

        $template = ManpowerTemplate::with(['items.jobPosition'])->find($state);

        if ($template) {
            $set('analysis_details.manpower_template_id', $state);

            $items = $template->items->map(function ($item) {
                return [
                    'job_position_id' => $item->job_position_id,
                    'count' => $item->count,
                    'salary' => $item->salary,
                    'total_salary' => $item->total_salary,
                ];
            })->toArray();

            $set('manpowerItems', $items);
        }

        self::calculateDirectCost($get, $set);
    }

    public static function handleCostingTemplateSelection($state, Set $set, Get $get): void
    {
        if (! $state) {
            $set('analysis_details.manual_costs', []);
            $set('analysis_details.costing_template_id', null);

            return;
        }

        $template = CostingTemplate::with(['costingTemplateItems.item'])->find($state);

        if ($template) {
            $set('analysis_details.costing_template_id', $state);

            $manualCosts = $get('analysis_details.manual_costs') ?: [];
            $updatedManualCosts = $manualCosts;

            foreach ($template->costingTemplateItems as $templateItem) {
                $item = $templateItem->item;
                if (! $item) {
                    continue;
                }

                $directCostCategoryId = $item->direct_cost_category_id;
                if (! $directCostCategoryId) {
                    continue;
                }

                $existingIndex = -1;
                foreach ($updatedManualCosts as $index => $mc) {
                    if (($mc['direct_cost_category_id'] ?? null) == $directCostCategoryId) {
                        $existingIndex = $index;
                        break;
                    }
                }

                $subItems = $item->subItems->map(fn ($si) => [
                    'name' => $si->name,
                    'amount' => 0,
                ])->toArray();

                if ($existingIndex >= 0) {
                    $updatedManualCosts[$existingIndex]['total_amount'] = 0;
                    $updatedManualCosts[$existingIndex]['description'] = 'Auto-filled from template: '.$template->name;
                    $updatedManualCosts[$existingIndex]['sub_items'] = $subItems;
                } else {
                    $updatedManualCosts[] = [
                        'direct_cost_category_id' => $directCostCategoryId,
                        'total_amount' => 0,
                        'description' => 'Auto-filled from template: '.$template->name,
                        'sub_items' => $subItems,
                    ];
                }
            }

            // Final Deduplication by Category ID
            $manualCosts = collect($updatedManualCosts)
                ->groupBy('direct_cost_category_id')
                ->map(fn ($group) => $group->last())
                ->values()
                ->toArray();

            $set('analysis_details.manual_costs', $manualCosts);
        }

        self::calculateDirectCost($get, $set);
    }

    protected static function getTemplateAttachmentLinkHtml($templateId, $type): string
    {
        if (! $templateId) {
            return '<div class="text-sm text-gray-500 italic">No template selected.</div>';
        }

        $template = $type === 'manpower'
            ? \Modules\CRM\Models\ManpowerTemplate::find($templateId)
            : \Modules\CRM\Models\CostingTemplate::find($templateId);

        if (! $template) {
            return '<div class="text-sm text-red-500 italic">Template record not found.</div>';
        }

        $media = $template->getFirstMedia('source_file');

        if (! $media) {
            return '<div class="text-sm text-amber-500 italic p-3 border border-dashed rounded-lg border-amber-200 bg-amber-50 dark:bg-amber-900/10">No attachment found in this template. Please re-upload the reference file in the CRM module if needed.</div>';
        }

        $url = $media->disk === 's3'
            ? $media->getTemporaryUrl(now()->addMinutes(60))
            : $media->getUrl();

        $icon = '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5H5.625Z" /><path d="M12.971 1.816A5.23 5.23 0 0 1 14.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 0 1 3.434 1.279 9.768 9.768 0 0 0-6.963-6.963Z" /></svg>';

        return sprintf(
            '<div class="flex items-center gap-4 p-4 border rounded-xl bg-white dark:bg-gray-900 border-primary-100 dark:border-primary-900 shadow-sm">
                <div class="p-2 rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600">
                    %s
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Reference Attachment</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">%s</p>
                </div>
                <a href="%s" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-semibold text-white bg-primary-600 rounded-lg hover:bg-primary-500 shadow-sm transition-all">
                    <span>Download</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 12m0 0l4.5-4.5M12 12V3" />
                    </svg>
                </a>
            </div>',
            $icon,
            $media->file_name,
            $url
        );
    }

    protected static function calculateMargin($revenue, $cost, $set): void
    {
        $revenue = self::parseNumericValue($revenue ?? 0);
        $cost = self::parseNumericValue($cost ?? 0);

        if ($revenue > 0) {
            $margin = (($revenue - $cost) / $revenue) * 100;
            $set('/margin_percentage', round($margin, 2));
        } else {
            $set('/margin_percentage', 0);
        }
    }

    public static function calculateSubItemAmount(Get $get, Set $set): void
    {
        $qty = self::parseNumericValue($get('quantity') ?? 1);
        $unitAmount = self::parseNumericValue($get('unit_amount') ?? 0);
        $total = $qty * $unitAmount;

        $set('amount', $total);

        // Sum up all sub-items to update the Category Total (parent field 'amount')
        $subItems = $get('../../sub_items') ?? [];
        $categoryTotal = collect($subItems)->sum(fn ($i) => self::parseNumericValue($i['amount'] ?? 0));

        // Push the sum to the Category Total field
        $set('../../amount', $categoryTotal);

        // Bubble up calculation to the top level global subtotal
        self::calculateDirectCost($get, $set);
    }

    public static function calculateSubItemAmountForIndirect(Get $get, Set $set)
    {
        $qty = self::parseNumericValue($get('quantity') ?? 1);
        $unitAmount = self::parseNumericValue($get('unit_amount') ?? 0);
        $total = $qty * $unitAmount;

        $set('amount', $total);

        // Sum up all sub-items to update the parent row's 'value'
        $subItems = $get('../../sub_items') ?? [];
        $categoryTotal = collect($subItems)->sum(fn ($i) => self::parseNumericValue($i['amount'] ?? 0));

        // Update the parent row amount
        $set('../../unit_cost_price', $categoryTotal);

        // Bubble up calculation to the Step 6 subtotal
        self::calculateIndirectCost($get, $set, '../../../../');
    }

    public static function calculateIndirectCost($get, $set, string $root = ''): void
    {
        $indirectCosts = $get($root.'analysis_details.indirect_costs') ?? $get('analysis_details.indirect_costs') ?? $get('/analysis_details.indirect_costs') ?? [];
        $total = 0;

        // Use the centralized monthly totals calculated by calculateDirectCost
        $revenueBasis = self::parseNumericValue($get($root.'revenue_per_month') ?? $get('/revenue_per_month') ?? 0);
        $costBasis = self::parseNumericValue($get($root.'direct_cost') ?? $get('/direct_cost') ?? 0);

        foreach ($indirectCosts as $cost) {
            $calcType = $cost['calculation_type'] ?? 'nominal';
            $value = self::parseNumericValue($cost['unit_cost_price'] ?? 0);

            if ($calcType === 'percentage') {
                $basis = $cost['percentage_basis'] ?? 'revenue';
                $basisValue = ($basis === 'revenue') ? $revenueBasis : $costBasis;
                $total += ($basisValue * ($value / 100));
            } else {
                $total += $value;
            }
        }

        // Update Subtotal in Step 6
        $set($root.'analysis_details.manual_indirect_total', $total);
        $set($root.'avg_monthly_indirect_cost', $total);

        // Trigger global performance recalculation
        self::calculatePerformance($get, $set, $root);
    }

    public static function calculatePerformance($get, $set, string $root = ''): void
    {
        // 1. Core Inputs
        $directCost = self::parseNumericValue($get($root.'direct_cost') ?? $get('/direct_cost') ?? 0);
        $indirectCost = self::parseNumericValue($get($root.'avg_monthly_indirect_cost') ?? $get('/avg_monthly_indirect_cost') ?? 0);
        $gpmTarget = self::parseNumericValue($get($root.'management_fee_rate') ?? $get('/management_fee_rate') ?? 15.00);
        $interestRate = self::parseNumericValue($get($root.'interest_rate') ?? $get('/interest_rate') ?? 1.50);
        $taxRate = self::parseNumericValue($get($root.'tax_rate') ?? $get('/tax_rate') ?? 22.00);
        $duration = self::getProjectDurationMonths($get, $root);
        $depreciation = self::parseNumericValue($get($root.'manual_depreciation') ?? $get('/manual_depreciation') ?? 0);

        // 2. Revenue Calculation (Target GPM / Cost-Plus Model)
        $nominalFee = self::parseNumericValue($get($root.'management_fee') ?? $get('/management_fee') ?? 0);

        if ($gpmTarget > 0) {
            // Formula: Revenue = Cost / (1 - Margin)
            $revenue = $directCost / (1 - ($gpmTarget / 100));
        } else {
            // Fallback to nominal fee if rate is not specified
            $revenue = $directCost + $nominalFee;
        }

        // 3. Performance Metrics (Monthly)
        $grossProfit = $revenue - $directCost;
        $ebitda = $grossProfit - $indirectCost;
        $ebit = $ebitda - $depreciation;
        $interestExpense = ($directCost + $indirectCost) * ($interestRate / 100);
        $ebt = $ebit - $interestExpense;
        $taxExpense = ($ebt > 0) ? ($ebt * ($taxRate / 100)) : 0;
        $netProfit = $ebt - $taxExpense;
        $npm = ($revenue > 0) ? ($netProfit / $revenue) * 100 : 0;

        // 4. Update Step 7 State (Monthly)
        $set($root.'revenue_per_month', $revenue);
        $set($root.'gross_profit', $grossProfit);
        $set($root.'ebitda', $ebitda);
        $set($root.'ebit', $ebit);
        $set($root.'ebt', $ebt);
        $set($root.'net_profit', $netProfit);
        $set($root.'net_profit_margin', $npm);

        // 5. Update Project Totals (Step 7 Bottom)
        $totalProjectRevenue = $revenue * $duration;
        $totalProjectCost = ($directCost + $indirectCost) * $duration;

        $set($root.'total_project_revenue', $totalProjectRevenue);
        $set($root.'total_project_cost', $totalProjectCost);
        $set($root.'margin_percentage', ($totalProjectRevenue > 0) ? (($totalProjectRevenue - $totalProjectCost) / $totalProjectRevenue) * 100 : 0);

        // EXTRA: Ensure dashboard always shows the latest basis costs
        $set($root.'direct_cost', $directCost);
        $set($root.'avg_monthly_indirect_cost', $indirectCost);
    }

    protected static function getManpowerPreviewHtml($templateId): string
    {
        if (! $templateId) {
            return 'No template selected.';
        }
        $record = ManpowerTemplate::with(['items.jobPosition'])->find($templateId);
        if (! $record) {
            return 'Template not found.';
        }

        $rows = '';
        $totalQuantity = 0;
        $totalBasic = 0;
        $totalBpjs = 0;
        $totalAll = 0;

        foreach ($record->items as $item) {
            $totalMonthly = (float) ($item->total_monthly_cost ?? 0);
            $basic = (float) ($item->basic_salary ?? 0);
            $bpjs = $totalMonthly - $basic;
            $qty = (int) ($item->quantity ?? 0);

            $totalQuantity += $qty;
            $totalBasic += $basic;
            $totalBpjs += $bpjs;
            $totalAll += $totalMonthly;

            $rows .= "<tr>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: left; background: white;'>{$item->jobPosition?->name}</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: center; background: white;'>{$qty}</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: right; background: white;'>Rp ".number_format($basic, 0, ',', '.')."</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: right; background: white;'>Rp ".number_format($bpjs, 0, ',', '.')."</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: right; background: white; font-weight: bold; color: #059669;'>Rp ".number_format($totalMonthly, 0, ',', '.').'</td>
            </tr>';
        }

        return "<div style='overflow-x: auto; border-radius: 8px; border: 1px solid #e5e7eb;'>
            <table style='width: 100%; border-collapse: collapse; font-size: 13px; font-family: inherit;'>
                <thead>
                    <tr style='background: #f9fafb;'>
                        <th style='border-bottom: 2px solid #e5e7eb; padding: 12px; text-align: left; font-weight: 600; color: #374151;'>Job Position</th>
                        <th style='border-bottom: 2px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600; color: #374151;'>Qty</th>
                        <th style='border-bottom: 2px solid #e5e7eb; padding: 12px; text-align: right; font-weight: 600; color: #374151;'>Basic Salary</th>
                        <th style='border-bottom: 2px solid #e5e7eb; padding: 12px; text-align: right; font-weight: 600; color: #374151;'>BPJS + Others</th>
                        <th style='border-bottom: 2px solid #e5e7eb; padding: 12px; text-align: right; font-weight: 600; color: #059669;'>Total Monthly</th>
                    </tr>
                </thead>
                <tbody>{$rows}</tbody>
                <tfoot>
                    <tr style='background: #f3f4f6; font-weight: bold;'>
                        <td style='border-top: 2px solid #e5e7eb; padding: 12px;'>TOTAL KALKULASI</td>
                        <td style='border-top: 2px solid #e5e7eb; padding: 12px; text-align: center;'>{$totalQuantity}</td>
                        <td style='border-top: 2px solid #e5e7eb; padding: 12px; text-align: right;'>Rp ".number_format($totalBasic, 0, ',', '.')."</td>
                        <td style='border-top: 2px solid #e5e7eb; padding: 12px; text-align: right;'>Rp ".number_format($totalBpjs, 0, ',', '.')."</td>
                        <td style='border-top: 2px solid #e5e7eb; padding: 12px; text-align: right; color: #059669;'>Rp ".number_format($totalAll, 0, ',', '.').'</td>
                    </tr>
                </tfoot>
            </table>
        </div>';
    }

    public static function getOperationalPreviewHtml($templateId): string
    {
        if (! $templateId) {
            return 'No template selected.';
        }
        $record = CostingTemplate::with(['costingTemplateItems.item'])->find($templateId);
        if (! $record) {
            return 'Template not found.';
        }

        $rows = '';
        $totalQty = 0;
        $totalInvestment = 0;
        $totalMonthlyImpact = 0;

        foreach ($record->costingTemplateItems as $item) {
            $uom = $item->unit ?? $item->item?->unitOfMeasure?->name ?? '-';
            $qty = (float) ($item->quantity ?? 1);
            $investment = (float) ($item->total_price ?? 0);
            $monthly = (float) ($item->monthly_cost ?? 0);

            $totalQty += $qty;
            $totalInvestment += $investment;
            $totalMonthlyImpact += $monthly;

            $rows .= "<tr>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: left; background: white;'>{$item->item?->name}</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: center; background: white;'>{$qty}</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: left; background: white;'>{$uom}</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: center; background: white;'>".($item->depreciation_months ?? 1)." Mo</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: right; background: white;'>Rp ".number_format((float) ($item->unit_price_markup ?? 0), 0, ',', '.')."</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: right; background: white;'>Rp ".number_format($investment, 0, ',', '.')."</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: right; background: white; font-weight: bold; color: #059669;'>Rp ".number_format($monthly, 0, ',', '.').'</td>
            </tr>';
        }

        return "<div style='overflow-x: auto; border-radius: 8px; border: 1px solid #e5e7eb;'>
            <table style='width: 100%; border-collapse: collapse; font-size: 13px; font-family: inherit;'>
                <thead>
                    <tr style='background: #f9fafb;'>
                        <th style='border-bottom: 2px solid #e5e7eb; padding: 12px; text-align: left; font-weight: 600; color: #374151;'>Item/Packet</th>
                        <th style='border-bottom: 2px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600; color: #374151;'>Qty</th>
                        <th style='border-bottom: 2px solid #e5e7eb; padding: 12px; text-align: left; font-weight: 600; color: #374151;'>UoM</th>
                        <th style='border-bottom: 2px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600; color: #374151;'>Depr.</th>
                        <th style='border-bottom: 2px solid #e5e7eb; padding: 12px; text-align: right; font-weight: 600; color: #374151;'>Unit Price</th>
                        <th style='border-bottom: 2px solid #e5e7eb; padding: 12px; text-align: right; font-weight: 600; color: #374151;'>Investment</th>
                        <th style='border-bottom: 2px solid #e5e7eb; padding: 12px; text-align: right; font-weight: 600; color: #059669;'>Monthly Impact</th>
                    </tr>
                </thead>
                <tbody>{$rows}</tbody>
                <tfoot>
                    <tr style='background: #f3f4f6; font-weight: bold;'>
                        <td style='border-top: 2px solid #e5e7eb; padding: 12px;' colspan='5'>TOTAL KALKULASI</td>
                        <td style='border-top: 2px solid #e5e7eb; padding: 12px; text-align: right;'>Rp ".number_format($totalInvestment, 0, ',', '.')."</td>
                        <td style='border-top: 2px solid #e5e7eb; padding: 12px; text-align: right; color: #059669;'>Rp ".number_format($totalMonthlyImpact, 0, ',', '.').'</td>
                    </tr>
                </tfoot>
            </table>
        </div>';
    }

    public static function calculateRepeaterItem(Get $get, Set $set, string $type): void
    {
        $qty = (float) ($get('quantity') ?? 1);
        $unitAmount = (float) ($get('unit_cost_price') ?? 0);
        $total = $qty * $unitAmount;
        $set('total_monthly_cost', $total);

        // Update the global totals and JSON
        self::syncItemsToManualCosts($get, $set, $type);
    }

    protected static function syncItemsToManualCosts(Get $get, Set $set, string $type): void
    {
        $categoryCode = $type === 'manpower' ? 'manpower' : 'tools_equipment';
        $categoryId = DirectCostCategory::where('code', $categoryCode)->first()?->id;

        if (! $categoryId) {
            return;
        }

        $items = $get("../../{$type}Items") ?? [];
        $manualCosts = $get('../../analysis_details.manual_costs') ?? [];

        $categoryIndex = collect($manualCosts)->search(fn ($c) => ($c['direct_cost_category_id'] ?? null) == $categoryId);

        if ($categoryIndex !== false) {
            $subItems = collect($items)->map(function ($item) {
                // If job position, get name
                $name = 'Item';
                if (! empty($item['costable_id'])) {
                    $posId = $item['costable_id'] ?? null;
                    $name = filled($posId) ? JobPosition::find($posId)?->name : 'Job Position';
                }

                return [
                    'name' => $name,
                    'job_position_id' => $item['costable_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_of_measure' => $item['unit_of_measure'] ?? 'Unit',
                    'unit_amount' => $item['unit_cost_price'],
                    'amount' => $item['total_monthly_cost'],
                    'risk_level' => $item['risk_level'] ?? 'very_low',
                    'employee_type' => $item['employee_type'] ?? 'ppu',
                    'is_labor_intensive' => $item['is_labor_intensive'] ?? false,
                    'bill_thr_monthly' => $item['bill_thr_monthly'] ?? true,
                    'bill_compensation_monthly' => $item['bill_compensation_monthly'] ?? true,
                    'include_non_fixed_in_accruals' => $item['include_non_fixed_in_accruals'] ?? false,
                    'extra_costs' => $item['extra_costs'] ?? [],
                    'cost_breakdown' => $item['cost_breakdown'] ?? null,
                ];
            })->toArray();

            $manualCosts[$categoryIndex]['sub_items'] = $subItems;
            $manualCosts[$categoryIndex]['amount'] = collect($subItems)->sum('amount');
            $set('../../analysis_details.manual_costs', $manualCosts);

            self::calculateDirectCost($get, $set, '../../');
        }
    }
}
