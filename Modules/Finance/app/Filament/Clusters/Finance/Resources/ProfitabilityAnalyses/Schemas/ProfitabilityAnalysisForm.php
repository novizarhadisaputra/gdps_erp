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
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Schemas\PaymentTermForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Schemas\ProductClusterForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Models\DirectCostCategory;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\PaymentTerm;
use Modules\MasterData\Models\PtkpConfig;
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
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            // Remove thousand separators (.) and replace decimal separator (,) with (.)
            $cleanValue = str_replace(['.', ','], ['', '.'], $value);

            return (float) $cleanValue;
        }

        return 0.0;
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
                                    ->relationship('generalInformation', 'document_number')
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
                                    ->placeholder('Auto-generated')
                                    ->columnSpan(1),
                                Hidden::make('lead_id'),
                                TextInput::make('revision_number')
                                    ->label('Revision #')
                                    ->disabled()
                                    ->default(0)
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
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $set('require_manpower_costing', false);
                                            $set('require_operational_costing', false);
                                            $set('manpower_template_id', null);
                                            $set('costing_template_id', null);
                                        }
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
                                    ->createOptionForm(PaymentTermForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->dehydrated(),
                            ]),
                    ]),

                Step::make('Manpower Requirements')
                    ->label('Manpower Planning')
                    ->description('Determine personnel needs based on job positions or manpower packets.')
                    ->icon(Heroicon::UserGroup)
                    ->disabled(fn ($record) => $record && ! in_array($record->status?->value ?? $record->status, [ProfitabilityAnalysisStatus::Draft->value, ProfitabilityAnalysisStatus::Rejected->value]))
                    ->visible(fn (Get $get) => (bool) $get('require_manpower_costing'))
                    ->schema([
                        Select::make('manpower_template_id')
                            ->label('Manpower Template')
                            ->options(ManpowerTemplate::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->hidden(fn (Get $get) => (bool) $get('is_manual_cost'))
                            ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::handleManpowerTemplateSelection($state, $set, $get)),

                        TextEntry::make('manpower_preview')
                            ->label('Personnel Summary')
                            ->state(fn (Get $get) => new HtmlString(self::getManpowerPreviewHtml($get('manpower_template_id'))))
                            ->html()
                            ->visible(fn (Get $get) => filled($get('manpower_template_id'))),

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
                    ->visible(fn (Get $get) => (bool) $get('require_operational_costing'))
                    ->schema([
                        Select::make('costing_template_id')
                            ->label('Operational Template')
                            ->options(CostingTemplate::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->hidden(fn (Get $get) => (bool) $get('is_manual_cost'))
                            ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::handleCostingTemplateSelection($state, $set, $get)),

                        TextEntry::make('operational_preview')
                            ->label('Equipment & Material Summary')
                            ->state(fn (Get $get) => new HtmlString(self::getOperationalPreviewHtml($get('costing_template_id'))))
                            ->html()
                            ->visible(fn (Get $get) => filled($get('costing_template_id'))),
                    ]),

                Step::make('Manual Costing')

                    ->label('Manual Cost Entry')
                    ->description('Enter high-level monthly direct costs and revenue.')
                    ->icon(Heroicon::Calculator)
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
                                                Grid::make(3)
                                                    ->schema([
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
                                                            ->label('Unit Price')
                                                            ->numeric()
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
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

                                                        // Granular Costing Fields (for Manpower)
                                                        Grid::make(5)
                                                            ->schema([
                                                                Select::make('risk_level')
                                                                    ->label('Risk')
                                                                    ->options([
                                                                        'very_low' => 'Very Low (0.24%)',
                                                                        'low' => 'Low (0.54%)',
                                                                        'medium' => 'Medium (0.89%)',
                                                                        'high' => 'High (1.27%)',
                                                                        'very_high' => 'Very High (1.74%)',
                                                                    ])
                                                                    ->default('very_low')
                                                                    ->required()
                                                                    ->live(),
                                                                Select::make('employee_type')
                                                                    ->label('Participation')
                                                                    ->options([
                                                                        'ppu' => 'PPU',
                                                                        'pbpu' => 'PBPU',
                                                                    ])
                                                                    ->default('ppu')
                                                                    ->required()
                                                                    ->live(),
                                                                Toggle::make('is_labor_intensive')
                                                                    ->label('Labor Intensive')
                                                                    ->default(false)
                                                                    ->live(),
                                                                Toggle::make('bill_thr_monthly')
                                                                    ->label('Bill THR')
                                                                    ->default(true)
                                                                    ->live(),
                                                                Toggle::make('bill_compensation_monthly')
                                                                    ->label('Bill Comp')
                                                                    ->default(true)
                                                                    ->live(),
                                                                Toggle::make('include_non_fixed_in_accruals')
                                                                    ->label('Incl Non-Fixed')
                                                                    ->default(false)
                                                                    ->live(),
                                                            ])
                                                            ->visible(function (Get $get) {
                                                                $categoryId = $get('../../direct_cost_category_id');
                                                                if (! $categoryId) {
                                                                    return false;
                                                                }
                                                                $category = DirectCostCategory::find($categoryId);

                                                                return $category && $category->code === 'manpower';
                                                            })
                                                            ->columnSpanFull(),
                                                    ]),
                                            ])
                                            ->collapsible()
                                            ->defaultItems(0)
                                            ->reorderableWithButtons()
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set, '../../')),
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
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set)),
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
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set)),
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
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set, '../../')),
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
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set, '../../')),
                                    ]),
                            ])
                            ->itemLabel(fn (array $state): ?string => DirectCostCategory::find($state['direct_cost_category_id'] ?? null)?->name ?? 'New Indirect Cost')
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set)),
                    ]),

                Step::make('Financial Performance')

                    ->label('Financial Performance')
                    ->description('Key monthly and project-wide financial metrics.')
                    ->icon(Heroicon::PresentationChartLine)
                    ->disabled(fn ($record) => $record && ! in_array($record->status?->value ?? $record->status, [ProfitabilityAnalysisStatus::Draft->value, ProfitabilityAnalysisStatus::Rejected->value]))
                    ->schema([
                        Section::make('Automated Cost Review')
                            ->description('Aggregated monthly costs calculated from personnel, tools, and indirect inputs.')
                            ->visible(fn (Get $get) => ! $get('is_manual_cost'))
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

    public static function getProjectDurationMonths($get): float
    {
        $startDate = $get('/start_date');
        $endDate = $get('/end_date');

        if ($startDate && $endDate) {
            $start = \Carbon\Carbon::parse($startDate);
            $end = \Carbon\Carbon::parse($endDate);
            $days = $start->diffInDays($end);

            return max(1, round($days / 30, 2));
        }

        $giId = $get('/general_information_id');
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
        $projectDurationMonths = self::getProjectDurationMonths($get);

        $totalProjectCost = 0;
        $totalProjectRevenue = 0;
        $totalProjectDepreciation = 0;
        $totalProjectIndirectCost = 0;

        $manpowerCostMonthly = 0;
        $toolsCostMonthly = 0;
        $materialCostMonthly = 0;

        $isManual = (bool) ($get('/is_manual_cost') ?? false);

        // 1. Calculate Manual Costs (Step 3) - ALWAYS DO THIS
        $totalStep3Cost = 0;
        $manualCosts = $get('/analysis_details.manual_costs') ?? [];

        $catManpowerId = DirectCostCategory::where('code', 'manpower')->first()?->id;
        $catToolsId = DirectCostCategory::where('code', 'tools_equipment')->first()?->id;
        $catMaterialId = DirectCostCategory::where('code', 'material')->first()?->id;

        foreach ($manualCosts as $item) {
            $amount = self::parseNumericValue($item['amount'] ?? 0);
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

        // Always update the manual revenue field in Step 3
        $set('/analysis_details.manual_revenue', $totalStep3Cost);
        $set('/direct_cost', $totalStep3Cost);

        if ($isManual || ! empty($manualCosts)) {
            $totalProjectCost = $totalStep3Cost * $projectDurationMonths;
            $totalProjectRevenue = $totalStep3Cost * $projectDurationMonths;
            $totalProjectDepreciation = self::parseNumericValue($get('/manual_depreciation') ?? 0) * $projectDurationMonths;

            // These are already monthly totals from Step 3 summation above
            // No need to divide by duration again, they are already the monthly target.
        } else {
            // First pass: Calculate Revenue and Direct Costs from Fixed/Nominal items
            // (We need an initial revenue estimate for percentage-based costs)
            $manpowerItems = $get('/manpowerItems') ?? [];
            $operationalItems = $get('/operationalItems') ?? [];

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
        $indirectItems = $get('/analysis_details.indirect_costs') ?? [];
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
        $mgmtFeeRate = self::parseNumericValue($get('/management_fee_rate') ?? 0);
        $avgMonthlyDirectCost = $projectDurationMonths > 0 ? ($totalProjectCost / $projectDurationMonths) : 0;
        $avgMonthlyIndirectCost = $projectDurationMonths > 0 ? ($totalProjectIndirectCost / $projectDurationMonths) : 0;

        if ($mgmtFeeRate > 0) {
            $calculatedMgmtFee = $avgMonthlyDirectCost * ($mgmtFeeRate / 100);
            $set('/management_fee', $calculatedMgmtFee);
            $mgmtFee = $calculatedMgmtFee;
        } else {
            $mgmtFee = (float) ($get('/management_fee') ?? 0);
        }

        // Add Management Fee to Revenue (Pro-rated monthly)
        $totalProjectRevenue += ($mgmtFee * $projectDurationMonths);

        $set('/total_project_cost', $totalProjectCost);
        $set('/total_project_revenue', $totalProjectRevenue);

        // Update Indirect Subtotal
        $set('/analysis_details.manual_indirect_total', $totalProjectIndirectCost);

        // Pro-rated values back to "Standard Monthly" for high-level summary
        $avgMonthlyRevenue = $projectDurationMonths > 0 ? ($totalProjectRevenue / $projectDurationMonths) : 0;
        $avgMonthlyCost = $projectDurationMonths > 0 ? ($totalProjectCost / $projectDurationMonths) : 0;
        $avgMonthlyDepreciation = $projectDurationMonths > 0 ? ($totalProjectDepreciation / $projectDurationMonths) : 0;

        $set('/direct_cost', $avgMonthlyCost);
        $set('/depreciation', $avgMonthlyDepreciation);
        $set('/revenue_per_month', $avgMonthlyRevenue);

        // Push Cost Breakdown to State
        $set('/direct_cost_manpower', $manpowerCostMonthly);
        $set('/direct_cost_tools', $toolsCostMonthly);
        $set('/direct_cost_material', $materialCostMonthly);
        $set('/avg_monthly_indirect_cost', $avgMonthlyIndirectCost);
        $set('/gross_profit', $avgMonthlyRevenue - $avgMonthlyCost);

        // Advanced Financial Tiers
        $interestRate = self::parseNumericValue($get('/interest_rate') ?? 0.0);
        $taxRate = self::parseNumericValue($get('/tax_rate') ?? 22.0);

        // EBITDA = Revenue - (Direct Cost Excl Depr) - Total Indirect Cost (Dynamic)
        $avgMonthlyCostExclDepr = $avgMonthlyCost - $avgMonthlyDepreciation;
        $ebitda = ($avgMonthlyRevenue - $avgMonthlyCostExclDepr) - $avgMonthlyIndirectCost;

        // EBIT = EBITDA - Depreciation
        $ebit = $ebitda - $avgMonthlyDepreciation;

        // Interest (Cost of Fund)
        $paymentTermId = $get('/payment_term_id');
        $paymentTerm = $paymentTermId ? PaymentTerm::find($paymentTermId) : null;
        $topDays = (float) ($paymentTerm?->days ?? 30);
        $interest = ($topDays / 30.0 * (self::parseNumericValue($interestRate) / 100)) * $avgMonthlyCost;

        $ebt = $ebit - $interest;

        $tax = $ebt > 0 ? ($ebt * ($taxRate / 100)) : 0;
        $netProfit = $ebt - $tax;
        $netProfitMargin = $avgMonthlyRevenue > 0 ? ($netProfit / $avgMonthlyRevenue) * 100 : 0;

        $set('/ebitda', $ebitda);
        $set('/ebit', $ebit);
        $set('/ebt', $ebt);
        $set('/net_profit', $netProfit);
        $set('/net_profit_margin', round($netProfitMargin, 2));

        // Recalculate margin (GP Margin)
        self::calculateMargin($avgMonthlyRevenue, $avgMonthlyCost, $set);

        // Debug state
        \Illuminate\Support\Facades\Log::info('calculateDirectCost Setting State:', call_user_func(function () use ($get) {
            // Try to dump the whole root state
            $rootState = [];
            try {
                $rootState = $get('');
            } catch (\Exception $e) {
            }
            if (empty($rootState)) {
                try {
                    $rootState = $get('../../..');
                } catch (\Exception $e) {
                }
            }

            return [
                'root' => $rootState,
                'target_revenue' => $get('/revenue_per_month'),
            ];
        }));
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
                ptkpCode: filled($get('ptkp_config_id')) ? PtkpConfig::find($get('ptkp_config_id'))?->code ?? 'TK/0' : 'TK/0',
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

    protected static function handleManpowerTemplateSelection($state, Set $set, Get $get): void
    {
        if (! $state) {
            return;
        }

        $record = ManpowerTemplate::with(['items.jobPosition'])->find($state);
        if (! $record) {
            return;
        }

        $service = app(ManpowerCostingService::class);
        $areaId = $get('project_area_id');
        $year = (int) ($get('year') ?? date('Y'));

        $manualCosts = $get('analysis_details.manual_costs') ?? [];
        $manpowerCategory = DirectCostCategory::where('code', 'manpower')->value('id');

        if (! $manpowerCategory) {
            return;
        }

        $subItems = [];
        $totalCategoryAmount = 0;

        foreach ($record->items as $item) {
            $jp = $item->jobPosition;
            if (! $jp) {
                continue;
            }

            $allowances = $item->allowances ?? [];

            $res = $service->calculate(
                basicSalary: (float) $item->basic_salary,
                allowances: $allowances,
                projectAreaId: $areaId,
                year: $year,
                riskLevel: $item->risk_level ?? 'very_low',
                isLaborIntensive: (bool) ($item->is_labor_intensive ?? false),
                employeeType: $item->employee_type ?? 'ppu',
                billThrMonthly: (bool) ($item->bill_thr_monthly ?? true),
                billCompensationMonthly: (bool) ($item->bill_compensation_monthly ?? true),
                includeNonFixedInAccruals: (bool) ($item->include_non_fixed_in_accruals ?? false),
                extraCosts: $item->extra_costs ?? [],
                ptkpCode: $item->ptkp_status ?? 'TK/0',
                isBpjsActive: (bool) ($item->is_bpjs_active ?? true)
            );

            // Apply Future Scaling Factor if defined
            $scale = 1 + ((float) ($item->future_adjustment_rate ?? 0) / 100);
            $unitCost = (float) ($res['total_direct_cost'] ?? 0) * $scale;
            $qty = (float) ($item->quantity ?? 1);
            $lineAmount = $unitCost * $qty;
            $totalCategoryAmount += $lineAmount;

            $subItems[] = [
                'name' => $jp->name,
                'job_position_id' => $jp->id,
                'quantity' => $qty,
                'unit_of_measure' => 'Org',
                'unit_amount' => $unitCost,
                'amount' => $lineAmount,
                'risk_level' => $item->risk_level ?? 'very_low',
                'employee_type' => $item->employee_type ?? 'ppu',
                'is_labor_intensive' => (bool) ($item->is_labor_intensive ?? false),
                'bill_thr_monthly' => (bool) ($item->bill_thr_monthly ?? true),
                'bill_compensation_monthly' => (bool) ($item->bill_compensation_monthly ?? true),
                'include_non_fixed_in_accruals' => (bool) ($item->include_non_fixed_in_accruals ?? false),
                'extra_costs' => $item->extra_costs ?? [],
                'cost_breakdown' => $res,
            ];
        }

        // Robust Update Logic: Map existing items and append if not found
        $found = false;
        $updatedManualCosts = collect($manualCosts)->map(function ($cost) use (&$found, $manpowerCategory, $subItems, $record) {
            if ((string) ($cost['direct_cost_category_id'] ?? '') === (string) $manpowerCategory) {
                $found = true;

                return array_merge($cost, [
                    'amount' => collect($subItems)->sum('amount'),
                    'description' => 'Manpower from Template: '.($record->name ?? 'Unnamed'),
                    'sub_items' => $subItems,
                    'direct_cost_category_id' => (string) $manpowerCategory,
                ]);
            }

            return $cost;
        })->toArray();

        if (! $found) {
            $updatedManualCosts[] = [
                'direct_cost_category_id' => (string) $manpowerCategory,
                'amount' => collect($subItems)->sum('amount'),
                'description' => 'Manpower from Template: '.($record->name ?? 'Unnamed'),
                'sub_items' => $subItems,
            ];
        }

        // Final Deduplication by Category ID
        $manualCosts = collect($updatedManualCosts)
            ->groupBy('direct_cost_category_id')
            ->map(fn ($group) => $group->last())
            ->values()
            ->toArray();

        $set('analysis_details.manual_costs', $manualCosts);

        // Also populate the table relationship state for granular persistence
        $manpowerCategoryId = (string) $manpowerCategory;
        $tableItems = collect($subItems)->map(function ($item) use ($manpowerCategoryId) {
            return [
                'costable_type' => JobPosition::class,
                'costable_id' => $item['job_position_id'],
                'quantity' => $item['quantity'],
                'unit_cost_price' => $item['unit_amount'],
                'total_monthly_cost' => $item['amount'],
                'direct_cost_category_id' => $manpowerCategoryId,
                'risk_level' => $item['risk_level'],
                'employee_type' => $item['employee_type'],
                'is_labor_intensive' => $item['is_labor_intensive'],
                'bill_thr_monthly' => $item['bill_thr_monthly'],
                'bill_compensation_monthly' => $item['bill_compensation_monthly'],
                'include_non_fixed_in_accruals' => $item['include_non_fixed_in_accruals'],
                'extra_costs' => $item['extra_costs'],
                'cost_breakdown' => $item['cost_breakdown'],
                'ptkp_config_id' => PtkpConfig::where('code', 'TK/0')->first()?->id, // Default
            ];
        })->toArray();

        $set('manpowerItems', $tableItems);

        self::calculateDirectCost($get, $set);
    }

    protected static function handleCostingTemplateSelection($state, Set $set, Get $get): void
    {
        if (! $state) {
            return;
        }

        $record = CostingTemplate::with('costingTemplateItems.item')->find($state);
        if (! $record) {
            return;
        }

        $manualCosts = $get('analysis_details.manual_costs') ?? [];
        $opCategory = DirectCostCategory::where('code', 'tools_equipment')->value('id');

        if (! $opCategory) {
            return;
        }

        $subItems = [];
        $totalCategoryAmount = 0;

        foreach ($record->costingTemplateItems as $item) {
            // monthly_cost in template IS already the total monthly amount for ALL specified units (including depreciation)
            $lineAmount = (float) ($item->monthly_cost ?? 0);
            $qty = (float) ($item->quantity ?? 1);
            $unitPrice = $qty > 0 ? ($lineAmount / $qty) : $lineAmount;

            $totalCategoryAmount += $lineAmount;

            $subItems[] = [
                'name' => $item->item?->name ?? 'Equipment Item',
                'quantity' => $qty,
                'unit_of_measure' => $item->unit ?? $item->item?->unitOfMeasure?->name ?? 'Unit',
                'unit_amount' => $unitPrice,
                'amount' => $lineAmount,
            ];
        }

        // Robust Update Logic: Map existing items and append if not found
        $found = false;
        $updatedManualCosts = collect($manualCosts)->map(function ($cost) use (&$found, $opCategory, $totalCategoryAmount, $subItems, $record) {
            if ((string) ($cost['direct_cost_category_id'] ?? '') === (string) $opCategory) {
                $found = true;

                return array_merge($cost, [
                    'amount' => $totalCategoryAmount,
                    'description' => 'Equipment from Template: '.($record->name ?? 'Unnamed'),
                    'sub_items' => $subItems,
                    'direct_cost_category_id' => (string) $opCategory,
                ]);
            }

            return $cost;
        })->toArray();

        if (! $found) {
            $updatedManualCosts[] = [
                'direct_cost_category_id' => (string) $opCategory,
                'amount' => $totalCategoryAmount,
                'description' => 'Equipment from Template: '.($record->name ?? 'Unnamed'),
                'sub_items' => $subItems,
            ];
        }

        // Final Deduplication by Category ID
        $manualCosts = collect($updatedManualCosts)
            ->groupBy('direct_cost_category_id')
            ->map(fn ($group) => $group->last())
            ->values()
            ->toArray();

        $set('analysis_details.manual_costs', $manualCosts);
        self::calculateDirectCost($get, $set);
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

        // Sum up all sub-items to update the Category Total (parent grid amount)
        $subItems = $get('../../sub_items') ?? [];
        $categoryTotal = collect($subItems)->sum(fn ($i) => self::parseNumericValue($i['amount'] ?? 0));

        // Update Category Total
        $set('../../amount', $categoryTotal);

        // Bubble up calculation
        self::calculateDirectCost($get, $set, '../../../');
    }

    public static function calculateSubItemAmountForIndirect(Get $get, Set $set)
    {
        $qty = self::parseNumericValue($get('quantity') ?? 1);
        $unitAmount = self::parseNumericValue($get('unit_amount') ?? 0);
        $total = $qty * $unitAmount;

        $set('amount', $total);

        // Sum up all sub-items to update the Category Total
        $subItems = $get('../../sub_items') ?? [];
        $categoryTotal = collect($subItems)->sum(fn ($i) => self::parseNumericValue($i['amount'] ?? 0));

        // Update Category Total
        $set('../../unit_cost_price', $categoryTotal);

        // Bubble up calculation
        self::calculateDirectCost($get, $set, '../../../');
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
        foreach ($record->items as $item) {
            $totalMonthly = (float) ($item->total_monthly_cost ?? 0);
            $basic = (float) ($item->basic_salary ?? 0);
            $bpjs = $totalMonthly - $basic; // Simple approximation or use detailed breakdown if available

            $rows .= "<tr>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: left; background: white;'>{$item->jobPosition?->name}</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: center; background: white;'>{$item->quantity}</td>
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
            </table>
        </div>";
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
        foreach ($record->costingTemplateItems as $item) {
            $uom = $item->unit ?? $item->item?->unitOfMeasure?->name ?? '-';
            $rows .= "<tr>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: left; background: white;'>{$item->item?->name}</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: center; background: white;'>{$item->quantity}</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: left; background: white;'>{$uom}</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: center; background: white;'>".($item->depreciation_months ?? 1)." Mo</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: right; background: white;'>Rp ".number_format((float) ($item->total_price ?? 0), 0, ',', '.')."</td>
                <td style='border: 1px solid #ddd; padding: 12px; text-align: right; background: white; font-weight: bold; color: #059669;'>Rp ".number_format((float) ($item->monthly_cost ?? 0), 0, ',', '.').'</td>
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
                        <th style='border-bottom: 2px solid #e5e7eb; padding: 12px; text-align: right; font-weight: 600; color: #374151;'>Investment</th>
                        <th style='border-bottom: 2px solid #e5e7eb; padding: 12px; text-align: right; font-weight: 600; color: #059669;'>Monthly Impact</th>
                    </tr>
                </thead>
                <tbody>{$rows}</tbody>
            </table>
        </div>";
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
