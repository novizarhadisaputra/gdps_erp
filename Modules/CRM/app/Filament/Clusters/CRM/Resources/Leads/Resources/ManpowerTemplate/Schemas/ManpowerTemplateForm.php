<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
use Illuminate\Support\HtmlString;
use Modules\Finance\Services\ManpowerCostingService;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Schemas\JobPositionForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Models\BpjsJhtConfig;
use Modules\MasterData\Models\BpjsJkkConfig;
use Modules\MasterData\Models\BpjsJkmConfig;
use Modules\MasterData\Models\BpjsJknCategory;
use Modules\MasterData\Models\BpjsJpConfig;
use Modules\MasterData\Models\BufferCostType;
use Modules\MasterData\Models\DirectCostCategory;
use Modules\MasterData\Models\FixedAllowance;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\MinimumWage;
use Modules\MasterData\Models\NonFixedAllowance;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\TaxObject;
use Modules\MasterData\Models\TaxPtkpConfig;
use Modules\MasterData\Models\Training;

class ManpowerTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema(self::schema());
    }

    public static function schema(): array
    {
        return [
            Wizard::make([
                Step::make('Costing Identification')
                    ->label(__('Costing Identification'))
                    ->description(__('Define basic costing details and project area.'))
                    ->icon('heroicon-m-identification')
                    ->schema([
                        TextInput::make('code')
                            ->hidden(fn (string $operation): bool => $operation === 'create')
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(255),
                        TextInput::make('name')
                            ->label(__('Costing Name'))
                            ->placeholder(__('e.g., Security Level 1, Admin Staff Proyek A'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('A descriptive name to identify this costing template.')),
                        TextInput::make('year')
                            ->label(__('Year'))
                            ->numeric()
                            ->default(date('Y'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if (! $state) {
                                    return;
                                }

                                $items = $get('items') ?? [];
                                foreach ($items as $iKey => $item) {
                                    $itemAreaId = $item['project_area_id'] ?? null;
                                    if ($itemAreaId) {
                                        $umk = MinimumWage::where('project_area_id', $itemAreaId)
                                            ->where('year', $state)
                                            ->where('is_active', true)
                                            ->first();

                                        if ($umk) {
                                            $set("items.{$iKey}.basic_salary", $umk->amount);
                                        }
                                    }
                                }
                            })
                            ->helperText(__('Determines the UMK year and tax regulations applied.')),
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->placeholder(__('Provide additional context if needed...'))
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label(__('Active Status'))
                            ->helperText(__('Only active templates can be selected in Profitability Analysis.'))
                            ->required()
                            ->default(true)
                            ->hidden(fn (string $operation): bool => $operation === 'create'),
                    ])
                    ->columns(2),

                Step::make('Personnel Composition')
                    ->label(__('Personnel Composition'))
                    ->description(__('Define personnel positions and associate them with Product Clusters (e.g., Aviation, FM) and basic salaries.'))
                    ->icon('heroicon-m-user-group')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->label(__('Personnel Composition'))
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        Select::make('product_cluster_id')
                                            ->label(__('Product Cluster'))
                                            ->relationship('productCluster', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->columnSpan(2),
                                        Select::make('job_position_id')
                                            ->label(__('Job Position'))
                                            ->placeholder(__('Select Position...'))
                                            ->relationship('jobPosition', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, Get $get, $state, $component) {
                                                if (! $state) {
                                                    return;
                                                }

                                                $areaId = $get('project_area_id');
                                                $year = $get('../../year') ?? $get('year') ?? date('Y');

                                                if (! $areaId) {
                                                    return;
                                                }

                                                $umk = MinimumWage::where('project_area_id', $areaId)
                                                    ->where('year', $year)
                                                    ->where('is_active', true)
                                                    ->first();
                                                if ($umk) {
                                                    $set('basic_salary', $umk->amount);
                                                }
                                            })
                                            ->createOptionForm(JobPositionForm::schema())
                                            ->createOptionAction(fn (Action $action) => $action->slideOver())
                                            ->columnSpan(2),
                                    ]),

                                Grid::make(4)
                                    ->schema([
                                        Select::make('project_area_id')
                                            ->label(__('Project Area'))
                                            ->relationship(
                                                name: 'projectArea',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: function ($query, $livewire) {
                                                    $customerId = $livewire instanceof ManageRelatedRecords
                                                        ? $livewire->getOwnerRecord()->customer_id
                                                        : null;

                                                    return $query->when($customerId, fn ($q) => $q->whereHas('customers', fn ($c) => $c->where($c->qualifyColumn('id'), $customerId)));
                                                }
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->placeholder(__('Select City/Regency'))
                                            ->createOptionForm(ProjectAreaForm::schema())
                                            ->createOptionAction(fn (Action $action) => $action->slideOver())
                                            ->createOptionUsing(function (array $data, $livewire) {
                                                $area = ProjectArea::create($data);
                                                $customerId = $livewire instanceof ManageRelatedRecords
                                                    ? $livewire->getOwnerRecord()->customer_id
                                                    : null;

                                                if ($customerId) {
                                                    $area->customers()->attach($customerId);
                                                }

                                                return $area->id;
                                            })
                                            ->editOptionForm(ProjectAreaForm::schema())
                                            ->editOptionAction(fn (Action $action) => $action->slideOver())
                                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                if (! $state) {
                                                    return;
                                                }

                                                $year = $get('../../year') ?? $get('year') ?? date('Y');

                                                $umk = MinimumWage::where('project_area_id', $state)
                                                    ->where('year', $year)
                                                    ->where('is_active', true)
                                                    ->first();

                                                if ($umk) {
                                                    $set('basic_salary', $umk->amount);
                                                }
                                            })
                                            ->columnSpan(1),
                                        TextInput::make('quantity')
                                            ->label(__('Qty'))
                                            ->numeric()
                                            ->placeholder(__('e.g., 5'))
                                            ->default(1)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->minValue(1)
                                            ->helperText(__('Number of personnel for this position.'))
                                            ->columnSpan(1),
                                        TextInput::make('basic_salary')
                                            ->label(__('Basic Salary'))
                                            ->placeholder('0.00')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->minValue(0)
                                            ->helperText(__('Base monthly salary (before allowances).'))
                                            ->columnSpan(1),
                                        Select::make('work_scheme_id')
                                            ->label(__('Work Scheme'))
                                            ->relationship('workScheme', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->placeholder(__('Select operational schedule'))
                                            ->columnSpan(1),
                                    ]),

                                Grid::make(4)
                                    ->schema([
                                        Select::make('contract_type_id')
                                            ->label(__('Contract Type'))
                                            ->placeholder(__('Select Contract Type'))
                                            ->relationship('contractType', 'name')
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->helperText(__('Employee contract classification (e.g., PKWT, PKWTT, MITRA).'))
                                            ->columnSpan(1),
                                        Select::make('ptkp_status')
                                            ->label(__('Tax Status (PTKP)'))
                                            ->placeholder(__('Select Status'))
                                            ->options(TaxPtkpConfig::pluck('code', 'code'))
                                            ->default('TK/0')
                                            ->required()
                                            ->searchable()
                                            ->live()
                                            ->helperText(__('Used to calculate PPh 21 personal tax relief.'))
                                            ->columnSpan(1),
                                        Select::make('tax_object_id')
                                            ->label(__('Objek Pajak (PPh 21)'))
                                            ->placeholder(__('Pilih Objek Pajak'))
                                            ->relationship('taxObject', 'name', modifyQueryUsing: fn ($query) => $query->where('is_active', true))
                                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} - {$record->name}")
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->default(fn () => TaxObject::where('is_default', true)->value('id'))
                                            ->live()
                                            ->helperText(__('Menentukan regulasi perhitungan PPh 21 yang diterapkan.'))
                                            ->columnSpan(2),
                                    ]),

                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('future_adjustment_rate')
                                            ->label(__('Salary Scaling (%)'))
                                            ->numeric()
                                            ->placeholder(__('e.g., 5.0'))
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->minValue(0)
                                            ->helperText(__('Percentage increase for future salary forecasts/scaling.'))
                                            ->columnSpan(1),
                                        TextEntry::make('scaling_increment_nominal')
                                            ->label(__('Scaling Increment'))
                                            ->state(function (Get $get) {
                                                $basicSalary = $get('basic_salary');
                                                if (is_string($basicSalary)) {
                                                    $basicSalary = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', $basicSalary));
                                                } else {
                                                    $basicSalary = (float) ($basicSalary ?? 0);
                                                }
                                                $rate = (float) ($get('future_adjustment_rate') ?? 0);
                                                $amount = $basicSalary * ($rate / 100);

                                                return 'IDR '.number_format($amount, 0, ',', '.');
                                            })
                                            ->columnSpan(1),
                                        TextEntry::make('scaled_basic_salary')
                                            ->label(__('Scaled Basic Salary'))
                                            ->state(function (Get $get) {
                                                $basicSalary = $get('basic_salary');
                                                if (is_string($basicSalary)) {
                                                    $basicSalary = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', $basicSalary));
                                                } else {
                                                    $basicSalary = (float) ($basicSalary ?? 0);
                                                }
                                                $rate = (float) ($get('future_adjustment_rate') ?? 0);
                                                $amount = $basicSalary * (1 + $rate / 100);

                                                return 'IDR '.number_format($amount, 0, ',', '.');
                                            })
                                            ->columnSpan(2),
                                    ]),

                                Section::make(__('Remuneration & BPJS Policy'))
                                    ->compact()
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                // Column 1
                                                Grid::make(1)
                                                    ->schema([
                                                        Section::make(__('BPJS Kesehatan (JKN)'))
                                                            ->compact()
                                                            ->schema([
                                                                Select::make('jkn_category')
                                                                    ->label(__('JKN Category'))
                                                                    ->options(fn () => BpjsJknCategory::query()->where('is_active', true)->pluck('name', 'code')->toArray())
                                                                    ->default('PPU')
                                                                    ->required()
                                                                    ->helperText(__('Specific JKN participation category for this role.')),
                                                                Select::make('bpjs_health_config_id')
                                                                    ->label(__('Tipe BPJS Kesehatan'))
                                                                    ->placeholder(__('Pilih Tipe...'))
                                                                    ->relationship('bpjsHealthConfig', 'name')
                                                                    ->preload()
                                                                    ->searchable()
                                                                    ->nullable()
                                                                    ->live()
                                                                    ->helperText(__('Konfigurasi BPJS Kesehatan.')),
                                                                Select::make('bpjs_kesehatan_basis_id')
                                                                    ->label(__('BPJS Kesehatan Basis'))
                                                                    ->placeholder(__('Select Basis'))
                                                                    ->relationship('bpjsKesehatanBasis', 'name')
                                                                    ->preload()
                                                                    ->required()
                                                                    ->helperText(__('Salary basis used for BPJS Kesehatan.')),
                                                                Toggle::make('is_employee_jkn_borne_by_company')
                                                                    ->label(__('Cover Employee JKN'))
                                                                    ->default(false)
                                                                    ->helperText(__('Company covers the 1% employee portion of BPJS Health.')),
                                                            ]),

                                                        Section::make(__('BPJS Ketenagakerjaan - JHT'))
                                                            ->compact()
                                                            ->schema([
                                                                Select::make('bpjs_jht_config_id')
                                                                    ->label(__('Tipe JHT'))
                                                                    ->placeholder(__('Pilih Tipe...'))
                                                                    ->relationship('bpjsJhtConfig', 'name')
                                                                    ->preload()
                                                                    ->searchable()
                                                                    ->nullable()
                                                                    ->live()
                                                                    ->helperText(__('Konfigurasi BPJS JHT.')),
                                                                Toggle::make('is_employee_jht_borne_by_company')
                                                                    ->label(__('Cover Employee JHT'))
                                                                    ->default(false)
                                                                    ->helperText(__('Company covers the 2% employee portion of BPJS JHT.')),
                                                            ]),

                                                        Section::make(__('BPJS Ketenagakerjaan - JKK & JKM'))
                                                            ->compact()
                                                            ->schema([
                                                                Grid::make(2)
                                                                    ->schema([
                                                                        Select::make('bpjs_jkk_config_id')
                                                                            ->label(__('Tipe JKK'))
                                                                            ->placeholder(__('Pilih Tipe...'))
                                                                            ->relationship('bpjsJkkConfig', 'name')
                                                                            ->preload()
                                                                            ->searchable()
                                                                            ->nullable()
                                                                            ->live()
                                                                            ->helperText(__('Konfigurasi BPJS JKK.')),
                                                                        Toggle::make('is_employee_jkk_borne_by_company')
                                                                            ->label(__('Cover Employee JKK'))
                                                                            ->default(false),
                                                                        Select::make('bpjs_jkm_config_id')
                                                                            ->label(__('Tipe JKM'))
                                                                            ->placeholder(__('Pilih Tipe...'))
                                                                            ->relationship('bpjsJkmConfig', 'name')
                                                                            ->preload()
                                                                            ->searchable()
                                                                            ->nullable()
                                                                            ->live()
                                                                            ->helperText(__('Konfigurasi BPJS JKM.')),
                                                                        Toggle::make('is_employee_jkm_borne_by_company')
                                                                            ->label(__('Cover Employee JKM'))
                                                                            ->default(false),
                                                                    ]),
                                                            ]),
                                                    ]),

                                                // Column 2
                                                Grid::make(1)
                                                    ->schema([
                                                        Section::make(__('THR & Kompensasi'))
                                                            ->compact()
                                                            ->schema([
                                                                Grid::make(2)
                                                                    ->schema([
                                                                        Select::make('thr_billing_method')
                                                                            ->label(__('THR Billing'))
                                                                            ->options([
                                                                                'monthly_accrual' => __('Monthly Accrual'),
                                                                                'one_time' => __('One-time Payment'),
                                                                            ])
                                                                            ->default('monthly_accrual')
                                                                            ->required()
                                                                            ->helperText(__('Determines if THR is accrued monthly.')),
                                                                        Select::make('thr_basis_id')
                                                                            ->label(__('THR Basis'))
                                                                            ->placeholder(__('Select Basis'))
                                                                            ->relationship('thrBasis', 'name')
                                                                            ->preload()
                                                                            ->required()
                                                                            ->helperText(__('Salary components included in THR.')),
                                                                        Select::make('compensation_billing_method')
                                                                            ->label(__('Comp. Billing'))
                                                                            ->options([
                                                                                'monthly_accrual' => __('Monthly Accrual'),
                                                                                'one_time' => __('One-time Payment'),
                                                                            ])
                                                                            ->default('monthly_accrual')
                                                                            ->required()
                                                                            ->helperText(__('Determines if compensation is accrued monthly.')),
                                                                        Select::make('compensation_basis_id')
                                                                            ->label(__('Comp. Basis'))
                                                                            ->placeholder(__('Select Basis'))
                                                                            ->relationship('compensationBasis', 'name')
                                                                            ->preload()
                                                                            ->required()
                                                                            ->helperText(__('Salary components included in Compensation.')),
                                                                    ]),
                                                            ]),

                                                        Section::make(__('BPJS Ketenagakerjaan - JP'))
                                                            ->compact()
                                                            ->schema([
                                                                Select::make('bpjs_jp_config_id')
                                                                    ->label(__('Tipe JP'))
                                                                    ->placeholder(__('Pilih Tipe...'))
                                                                    ->relationship('bpjsJpConfig', 'name')
                                                                    ->preload()
                                                                    ->searchable()
                                                                    ->nullable()
                                                                    ->live()
                                                                    ->helperText(__('Konfigurasi BPJS JP.')),
                                                                Toggle::make('is_employee_jp_borne_by_company')
                                                                    ->label(__('Cover Employee JP'))
                                                                    ->default(false)
                                                                    ->helperText(__('Company covers the 1% employee portion of BPJS JP.')),
                                                            ]),

                                                        Section::make(__('Umum & Perpajakan (PPh 21)'))
                                                            ->compact()
                                                            ->schema([
                                                                Toggle::make('is_bpjs_active')
                                                                    ->label(__('BPJS Active'))
                                                                    ->default(true)
                                                                    ->live()
                                                                    ->helperText(__('Enable/disable all BPJS calculations.')),
                                                                Select::make('bpjs_ketenagakerjaan_basis_id')
                                                                    ->label(__('BPJS Ketenagakerjaan Basis'))
                                                                    ->placeholder(__('Select Basis'))
                                                                    ->relationship('bpjsKetenagakerjaanBasis', 'name')
                                                                    ->preload()
                                                                    ->required()
                                                                    ->helperText(__('Salary basis used for BPJS Ketenagakerjaan.')),
                                                                Grid::make(2)
                                                                    ->schema([
                                                                        Toggle::make('use_ter_method')
                                                                            ->label(__('Use TER Method (PPh 21)'))
                                                                            ->default(true)
                                                                            ->live()
                                                                            ->helperText(__('Use 2024 TER method. Disable for Progressive.')),
                                                                        Toggle::make('is_tax_borne_by_company')
                                                                            ->label(__('Tax Borne by Co.'))
                                                                            ->default(false)
                                                                            ->live()
                                                                            ->helperText(__('If enabled, company pays the PPh 21 tax.')),
                                                                    ]),
                                                            ]),
                                                    ]),
                                            ]),
                                    ]),

                                Section::make(__('Allowances & Extra Costs'))
                                    ->compact()
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Repeater::make('allowances')
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        Select::make('category')
                                                            ->options([
                                                                'fixed' => __('Fixed Allowance'),
                                                                'non_fixed' => __('Non-Fixed Allowance'),
                                                            ])
                                                            ->required()
                                                            ->live()
                                                            ->afterStateUpdated(fn ($state, Set $set) => $set('is_fixed', $state === 'fixed')),
                                                        Select::make('name')
                                                            ->label(__('Allowance Name'))
                                                            ->options(function (Get $get) {
                                                                $options = [];
                                                                if ($get('category') === 'fixed') {
                                                                    $options = FixedAllowance::pluck('name', 'name')->toArray();
                                                                } elseif ($get('category') === 'non_fixed') {
                                                                    $options = NonFixedAllowance::pluck('name', 'name')->toArray();
                                                                }

                                                                return collect($options)->mapWithKeys(function ($item, $key) {
                                                                    $cleanName = str_replace([' (Tetap)', ' (Tidak Tetap)'], '', $item);

                                                                    return [$key => $cleanName];
                                                                })->toArray();
                                                            })
                                                            ->searchable()
                                                            ->required(),
                                                        Select::make('type')
                                                            ->label(__('Type'))
                                                            ->options(['nominal' => __('Nominal'), 'percentage' => __('Percentage (%)')])
                                                            ->required()
                                                            ->live()
                                                            ->default('nominal'),
                                                        TextInput::make('value')
                                                            ->label(__('Value'))
                                                            ->numeric()
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                            ->live(onBlur: true)
                                                            ->required()
                                                            ->minValue(0),
                                                        Select::make('base_type')
                                                            ->label(__('Multiplier Base'))
                                                            ->options([
                                                                'basic_salary' => __('Gaji Pokok'),
                                                                'umk' => __('UMK'),
                                                            ])
                                                            ->default('basic_salary')
                                                            ->visible(fn (Get $get) => $get('type') === 'percentage')
                                                            ->required(fn (Get $get) => $get('type') === 'percentage'),
                                                        Select::make('frequency')
                                                            ->label(__('Frequency'))
                                                            ->options(['monthly' => __('Monthly'), 'daily' => __('Daily (Per Attendance)')])
                                                            ->required()
                                                            ->default('monthly'),
                                                        Hidden::make('is_fixed'),
                                                    ]),
                                            ])->defaultItems(0)->addActionLabel(__('Add Allowance')),

                                        Repeater::make('extra_costs')
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        Select::make('category')
                                                            ->label(__('Category'))
                                                            ->options([
                                                                'equipment' => __('Equipment / Items'),
                                                                'training' => __('Training'),
                                                                'buffer' => __('Buffer Cost / Inval'),
                                                                'other' => __('Other Direct Cost'),
                                                            ])
                                                            ->required()
                                                            ->live(),
                                                        Select::make('name')
                                                            ->label(__('Item Name'))
                                                            ->options(function (Get $get) {
                                                                $cat = $get('category');
                                                                if ($cat === 'equipment') {
                                                                    return Item::pluck('name', 'name');
                                                                }
                                                                if ($cat === 'training') {
                                                                    return Training::pluck('name', 'name');
                                                                }
                                                                if ($cat === 'buffer') {
                                                                    return BufferCostType::pluck('name', 'name');
                                                                }

                                                                return DirectCostCategory::pluck('name', 'name');
                                                            })
                                                            ->searchable()
                                                            ->required()
                                                            ->live()
                                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                                if (! $state) {
                                                                    return;
                                                                }
                                                                $cat = $get('category');
                                                                $cost = 0;
                                                                if ($cat === 'equipment') {
                                                                    $cost = Item::where('name', $state)->value('price') ?? 0;
                                                                } elseif ($cat === 'training') {
                                                                    $cost = Training::where('name', $state)->value('base_cost') ?? 0;
                                                                }

                                                                if ($cost > 0) {
                                                                    $set('annual_amount', $cost);
                                                                    $set('amount', round((float) $cost / 12, 0));
                                                                }
                                                            }),
                                                        TextInput::make('annual_amount')
                                                            ->label(__('Annual Budget'))
                                                            ->numeric()
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                            ->placeholder(__('0'))
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->minValue(0)
                                                            ->afterStateUpdated(fn ($state, Set $set) => $set('amount', round((float) ($state ?? 0) / 12, 0)))
                                                            ->helperText(__('Divided by 12 for monthly costing.')),
                                                        Hidden::make('amount')->dehydrated(),
                                                    ]),
                                            ])->defaultItems(0)->addActionLabel(__('Add Extra Cost')),
                                    ]),
                            ])
                            ->columnSpanFull()
                            ->defaultItems(1)
                            ->addActionLabel(__('Add Personnel Position'))
                            ->itemLabel(fn (array $state): ?string => filled($state['job_position_id'] ?? null) ? JobPosition::find($state['job_position_id'])?->name : __('New Position'))
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $set('simulation_trigger', uniqid());
                            }),
                    ])
                    ->columns(2),

                Step::make(__('Cost Simulation'))
                    ->label(__('Cost Simulation'))
                    ->description(__('Review projected monthly cost details grouped by cluster.'))
                    ->icon('heroicon-m-calculator')
                    ->schema([
                        TextEntry::make('cost_simulation_table')
                            ->label(__('Projected Cost Breakdown'))
                            ->html()
                            ->state(function (Get $get) {
                                $items = $get('items') ?? [];

                                if (empty($items)) {
                                    return new HtmlString('<div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-500"><p class="text-sm">'.__('Please complete the previous steps to view the cost simulation.').'</p></div>');
                                }

                                $service = app(ManpowerCostingService::class);
                                $totalTemplateCost = 0;
                                $tableContent = '';

                                // Group items by product_cluster_id
                                $groupedItems = [];
                                foreach ($items as $item) {
                                    $clusterId = $item['product_cluster_id'] ?? 'none';
                                    $groupedItems[$clusterId][] = $item;
                                }

                                foreach ($groupedItems as $clusterId => $clusterItems) {
                                    if ($clusterId !== 'none') {
                                        $clusterName = ProductCluster::find($clusterId)?->name ?? __('Unnamed Cluster');
                                    } else {
                                        $clusterName = __('General / No Cluster');
                                    }

                                    $tableContent .= "<tr class='bg-gray-100/50 dark:bg-gray-800/80'><td colspan='8' class='px-2 py-2 font-bold text-gray-900 dark:text-white uppercase text-[10px]'>".__('Cluster').": {$clusterName}</td></tr>";

                                    foreach ($clusterItems as $item) {
                                        $jpId = $item['job_position_id'] ?? null;
                                        $qty = (int) ($item['quantity'] ?? 0);
                                        $itemAreaId = $item['project_area_id'] ?? null;
                                        $itemWorkSchemeId = $item['work_scheme_id'] ?? null;

                                        if (! $jpId || $qty <= 0 || ! $itemAreaId || ! $itemWorkSchemeId) {
                                            continue;
                                        }

                                        $jp = JobPosition::find($jpId);
                                        if (! $jp) {
                                            continue;
                                        }

                                        $jknCategory = $item['jkn_category'] ?? 'PPU';
                                        $thrMethod = $item['thr_billing_method'] ?? 'monthly_accrual';
                                        $compMethod = $item['compensation_billing_method'] ?? 'monthly_accrual';

                                        // Automatically resolve risk level and employee type from chosen BPJS configurations
                                        $jkkConfigId = $item['bpjs_jkk_config_id'] ?? null;
                                        $jkkConfig = $jkkConfigId ? BpjsJkkConfig::find($jkkConfigId) : null;
                                        $riskLevel = $jkkConfig ? ($jkkConfig->risk_level ?? 'very_low') : 'very_low';
                                        $employeeType = strtolower($jknCategory);

                                        $jkmConfigId = $item['bpjs_jkm_config_id'] ?? null;
                                        $jkmConfig = $jkmConfigId ? BpjsJkmConfig::find($jkmConfigId) : null;

                                        $jhtConfigId = $item['bpjs_jht_config_id'] ?? null;
                                        $jhtConfig = $jhtConfigId ? BpjsJhtConfig::find($jhtConfigId) : null;

                                        $jpConfigId = $item['bpjs_jp_config_id'] ?? null;
                                        $jpConfig = $jpConfigId ? BpjsJpConfig::find($jpConfigId) : null;

                                        $res = $service->calculate(
                                            basicSalary: (float) ($item['basic_salary'] ?? 0),
                                            allowances: $item['allowances'] ?? [],
                                            projectAreaId: $itemAreaId,
                                            year: $get('year') ?? date('Y'),
                                            workSchemeId: $itemWorkSchemeId,
                                            riskLevel: $riskLevel,
                                            employeeType: $employeeType,
                                            jknCategory: $jknCategory,
                                            thrBillingMethod: $thrMethod,
                                            compensationBillingMethod: $compMethod,
                                            thrBasisId: $item['thr_basis_id'] ?? null,
                                            compensationBasisId: $item['compensation_basis_id'] ?? null,
                                            bpjsKesehatanBasisId: $item['bpjs_kesehatan_basis_id'] ?? null,
                                            bpjsKetenagakerjaanBasisId: $item['bpjs_ketenagakerjaan_basis_id'] ?? null,
                                            bpjsHealthConfigId: $item['bpjs_health_config_id'] ?? null,
                                            bpjsJkkConfigId: $item['bpjs_jkk_config_id'] ?? null,
                                            bpjsJkmConfigId: $item['bpjs_jkm_config_id'] ?? null,
                                            bpjsJhtConfigId: $item['bpjs_jht_config_id'] ?? null,
                                            bpjsJpConfigId: $item['bpjs_jp_config_id'] ?? null,
                                            billThrMonthly: (bool) ($item['bill_thr_monthly'] ?? true),
                                            billCompensationMonthly: (bool) ($item['bill_compensation_monthly'] ?? true),
                                            includeNonFixedInAccruals: (bool) ($item['include_non_fixed_in_accruals'] ?? false),
                                            extraCosts: $item['extra_costs'] ?? [],
                                            ptkpCode: $item['ptkp_status'] ?? 'TK/0',
                                            isBpjsActive: (bool) ($item['is_bpjs_active'] ?? true),
                                            useTerMethod: (bool) ($item['use_ter_method'] ?? true),
                                            borneByCompany: [
                                                'tax' => (bool) ($item['is_tax_borne_by_company'] ?? false),
                                                'jkn' => (bool) ($item['is_employee_jkn_borne_by_company'] ?? false),
                                                'jkk' => (bool) ($item['is_employee_jkk_borne_by_company'] ?? false),
                                                'jkm' => (bool) ($item['is_employee_jkm_borne_by_company'] ?? false),
                                                'jht' => (bool) ($item['is_employee_jht_borne_by_company'] ?? false),
                                                'jp' => (bool) ($item['is_employee_jp_borne_by_company'] ?? false),
                                            ],
                                            contractTypeId: $item['contract_type_id'] ?? null,
                                            taxObjectId: $item['tax_object_id'] ?? null
                                        );

                                        $scale = 1 + ((float) ($item['future_adjustment_rate'] ?? 0) / 100);
                                        $unitCost = $res['total_direct_cost'] * $scale;
                                        $lineTotal = $unitCost * $qty;
                                        $totalTemplateCost += $lineTotal;

                                        $fmt = fn ($val) => number_format($val, 0, ',', '.');
                                        $subA = ($res['upah'] + $res['allowances']['non_fixed']) * $scale;
                                        $subB = ($res['accruals']['thr'] + $res['accruals']['compensation']) * $scale;
                                        $subC = $res['bpjs_total'] * $scale;
                                        $subD = $res['pph21']['total'] * $scale;
                                        $subE = $res['extra_costs_total'] * $scale;

                                        // Detailed breakdown variables scaled
                                        $scaled_gapok = ($res['upah'] - $res['allowances']['fixed']) * $scale;
                                        $scaled_fixed = $res['allowances']['fixed'] * $scale;
                                        $scaled_non_fixed = $res['allowances']['non_fixed'] * $scale;
                                        $scaled_upah = $res['upah'] * $scale;

                                        $jkn_base = ($res['bpjs_health']['base'] ?? 0) * $scale;
                                        $jkn_employer = ($res['bpjs_health']['employer'] ?? 0) * $scale;
                                        $jkn_employee = ($res['bpjs_health']['employee'] ?? 0) * $scale;
                                        $jkn_employer_rate_pct = ($res['bpjs_health']['employer_rate'] ?? 0) * 100;
                                        $jkn_employee_rate_pct = ($res['bpjs_health']['employee_rate'] ?? 0) * 100;
                                        $scaled_jkn_total = ($res['bpjs_health']['employer_total'] ?? 0) * $scale;
                                        $borne_jkn = (bool) ($item['is_employee_jkn_borne_by_company'] ?? false)
                                            ? "<span class='text-green-600 dark:text-green-400 font-semibold'>(Ditanggung)</span>"
                                            : "<span class='text-gray-400 dark:text-gray-500'>(Potong Gaji)</span>";

                                        $jkk_base = ($res['bpjs_employment']['details']['jkk']['base'] ?? 0) * $scale;
                                        $jkk_employer = ($res['bpjs_employment']['details']['jkk']['employer'] ?? 0) * $scale;
                                        $jkk_employee = ($res['bpjs_employment']['details']['jkk']['employee'] ?? 0) * $scale;
                                        $jkk_total = ($res['bpjs_employment']['details']['jkk']['line_total'] ?? 0) * $scale;
                                        $jkk_rate_pct = $jkkConfig && $jkkConfig->has_tier ? 'Tier' : ($jkkConfig ? (float) $jkkConfig->employer_rate * 100 : 0.0);
                                        $jkk_employee_rate_pct = $jkkConfig ? (float) $jkkConfig->employee_rate * 100 : 0.0;
                                        $borne_jkk = (bool) ($item['is_employee_jkk_borne_by_company'] ?? false)
                                            ? "<span class='text-green-600 dark:text-green-400 font-semibold'>(Ditanggung)</span>"
                                            : "<span class='text-gray-400 dark:text-gray-500'>(Potong Gaji)</span>";

                                        $jkm_base = ($res['bpjs_employment']['details']['jkm']['base'] ?? 0) * $scale;
                                        $jkm_employer = ($res['bpjs_employment']['details']['jkm']['employer'] ?? 0) * $scale;
                                        $jkm_employee = ($res['bpjs_employment']['details']['jkm']['employee'] ?? 0) * $scale;
                                        $jkm_total = ($res['bpjs_employment']['details']['jkm']['line_total'] ?? 0) * $scale;
                                        $jkm_rate_pct = $jkmConfig ? (float) $jkmConfig->employer_rate * 100 : 0.0;
                                        $jkm_employee_rate_pct = $jkmConfig ? (float) $jkmConfig->employee_rate * 100 : 0.0;
                                        $borne_jkm = (bool) ($item['is_employee_jkm_borne_by_company'] ?? false)
                                            ? "<span class='text-green-600 dark:text-green-400 font-semibold'>(Ditanggung)</span>"
                                            : "<span class='text-gray-400 dark:text-gray-500'>(Potong Gaji)</span>";

                                        $jht_base = ($res['bpjs_employment']['details']['jht']['base'] ?? 0) * $scale;
                                        $jht_employer = ($res['bpjs_employment']['details']['jht']['employer'] ?? 0) * $scale;
                                        $jht_employee = ($res['bpjs_employment']['details']['jht']['employee'] ?? 0) * $scale;
                                        $jht_total = ($res['bpjs_employment']['details']['jht']['line_total'] ?? 0) * $scale;
                                        $jht_employer_rate_pct = $jhtConfig && $jhtConfig->has_tier ? 'Tier' : ($jhtConfig ? (float) $jhtConfig->employer_rate * 100 : 0.0);
                                        $jht_employee_rate_pct = $jhtConfig ? (float) $jhtConfig->employee_rate * 100 : 0.0;
                                        $borne_jht = (bool) ($item['is_employee_jht_borne_by_company'] ?? false)
                                            ? "<span class='text-green-600 dark:text-green-400 font-semibold'>(Ditanggung)</span>"
                                            : "<span class='text-gray-400 dark:text-gray-500'>(Potong Gaji)</span>";

                                        $jp_base = ($res['bpjs_employment']['details']['jp']['base'] ?? 0) * $scale;
                                        $jp_employer = ($res['bpjs_employment']['details']['jp']['employer'] ?? 0) * $scale;
                                        $jp_employee = ($res['bpjs_employment']['details']['jp']['employee'] ?? 0) * $scale;
                                        $jp_total = ($res['bpjs_employment']['details']['jp']['line_total'] ?? 0) * $scale;
                                        $jp_employer_rate_pct = $jpConfig ? (float) $jpConfig->employer_rate * 100 : 0.0;
                                        $jp_employee_rate_pct = $jpConfig ? (float) $jpConfig->employee_rate * 100 : 0.0;
                                        $borne_jp = (bool) ($item['is_employee_jp_borne_by_company'] ?? false)
                                            ? "<span class='text-green-600 dark:text-green-400 font-semibold'>(Ditanggung)</span>"
                                            : "<span class='text-gray-400 dark:text-gray-500'>(Potong Gaji)</span>";

                                        $scaled_thr_basis = $res['accruals']['basis'] * $scale;
                                        $scaled_thr = $res['accruals']['thr'] * $scale;
                                        $scaled_comp = $res['accruals']['compensation'] * $scale;

                                        $scaled_bruto = ($res['pph21']['bruto'] ?? 0) * $scale;
                                        $scaled_tax = ($res['pph21']['total'] ?? 0) * $scale;
                                        $tax_rate_pct = $res['pph21']['rate'] ?? 0.0;
                                        $tax_method = (bool) ($item['use_ter_method'] ?? true) ? 'TER' : 'Progresif Psl 17';
                                        $borne_tax = (bool) ($item['is_tax_borne_by_company'] ?? false)
                                            ? "<span class='text-green-600 dark:text-green-400 font-semibold'>(Ditanggung Perusahaan)</span>"
                                            : "<span class='text-gray-400 dark:text-gray-500'>(Potong Gaji)</span>";

                                        $extra_costs_rows_html = "
                                            <tr class='bg-slate-50/30 dark:bg-slate-800/10 font-semibold text-slate-900 dark:text-white'>
                                                <td class='py-1.5 px-3 text-center'>5</td>
                                                <td colspan='5' class='py-1.5 px-3 font-semibold text-primary-600 dark:text-primary-400'>5. BIAYA EKSTRA (EXTRA COSTS)</td>
                                                <td class='py-1.5 px-3 text-right font-bold'>Rp {$fmt($subE)}</td>
                                                <td class='py-1.5 px-3 text-right font-bold text-primary-600 dark:text-primary-400'>Rp {$fmt($subE * $qty)}</td>
                                            </tr>
                                        ";
                                        if (! empty($res['extra_costs'])) {
                                            $idx = 1;
                                            foreach ($res['extra_costs'] as $ec) {
                                                $ecName = $ec['name'] ?? __('Unnamed Cost');
                                                $ecVal = (float) ($ec['value'] ?? $ec['amount'] ?? 0) * $scale;
                                                $extra_costs_rows_html .= "
                                                    <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                        <td class='py-1.5 px-3 text-center text-slate-400'>5.{$idx}</td>
                                                        <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>{$ecName}</td>
                                                        <td class='py-1.5 px-3'>Input Biaya Ekstra</td>
                                                        <td class='py-1.5 px-3'>-</td>
                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                        <td class='py-1.5 px-3 text-right'>-</td>
                                                        <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($ecVal)}</td>
                                                        <td class='py-1.5 px-3 text-right'>Rp {$fmt($ecVal * $qty)}</td>
                                                    </tr>
                                                ";
                                                $idx++;
                                            }
                                        } else {
                                            $extra_costs_rows_html .= "
                                                <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                    <td class='py-1.5 px-3 text-center text-slate-400'>-</td>
                                                    <td colspan='5' class='py-1.5 px-3 text-slate-400 italic'>Tidak ada biaya ekstra tambahan (No extra costs configured)</td>
                                                    <td class='py-1.5 px-3 text-right text-slate-400'>Rp 0</td>
                                                    <td class='py-1.5 px-3 text-right text-slate-400'>Rp 0</td>
                                                </tr>
                                            ";
                                        }

                                        $tableContent .= "
                                             <tr class='border-b hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-xs align-top'>
                                                 <td class='px-2 py-3'>
                                                     <div class='font-semibold text-gray-900 dark:text-gray-100'>{$jp->name}</div>
                                                     <div class='text-[9px] text-gray-400'>{$jp->code} | PTKP: ".($item['ptkp_status'] ?? 'TK/0')."</div>
                                                 </td>
                                                 <td class='px-2 py-3 text-center font-bold'>{$qty}</td>
                                                 <td class='px-2 py-3 text-right'>Rp {$fmt($subA)}</td>
                                                 <td class='px-2 py-3 text-right'>Rp {$fmt($subB)}</td>
                                                 <td class='px-2 py-3 text-right'>Rp {$fmt($subC)}</td>
                                                 <td class='px-2 py-3 text-right'>Rp {$fmt($subD)}</td>
                                                 <td class='px-2 py-3 text-right'>Rp {$fmt($subE)}</td>
                                                 <td class='px-2 py-3 text-right font-bold text-primary-600'>Rp {$fmt($unitCost)}</td>
                                             </tr>
                                             <tr class='bg-gray-50/30 dark:bg-gray-900/10 border-b'>
                                                 <td colspan='8' class='px-4 py-2.5'>
                                                     <details class='group select-none'>
                                                         <summary class='cursor-pointer text-[10px] font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 flex items-center gap-1 focus:outline-none py-1 select-none'>
                                                             <svg class='w-3.5 h-3.5 shrink-0' width='14' height='14' style='width: 14px; height: 14px;' fill='none' stroke='currentColor' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'></path></svg>
                                                             <span>Lihat Rincian Rumus (Spreadsheet) - {$jp->name}</span>
                                                         </summary>
                                                         <div class='mt-2.5 relative overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900'>
                                                             <table class='w-full text-[10px] text-left text-slate-600 dark:text-slate-400 border-collapse'>
                                                                 <thead>
                                                                     <tr class='bg-slate-100 dark:bg-slate-800 text-[9px] uppercase tracking-wider text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800 font-bold'>
                                                                         <th class='py-2 px-3 text-center w-10'>No.</th>
                                                                         <th class='py-2 px-3'>Komponen Biaya (Cost Component)</th>
                                                                         <th class='py-2 px-3'>Dasar Perhitungan (Calculation Base)</th>
                                                                         <th class='py-2 px-3'>Formula / Rate</th>
                                                                         <th class='py-2 px-3 text-right'>Bagian Perusahaan (Employer)</th>
                                                                         <th class='py-2 px-3 text-right'>Bagian Karyawan (Employee)</th>
                                                                         <th class='py-2 px-3 text-right w-32'>Subtotal / Pax</th>
                                                                         <th class='py-2 px-3 text-right w-32'>Total Cost (Qty: {$qty})</th>
                                                                     </tr>
                                                                 </thead>
                                                                 <tbody class='divide-y divide-slate-100 dark:divide-slate-800'>
                                                                     <!-- 1. Dasar Upah -->
                                                                     <tr class='bg-slate-50/30 dark:bg-slate-800/10 font-semibold text-slate-900 dark:text-white'>
                                                                         <td class='py-1.5 px-3 text-center'>1</td>
                                                                         <td colspan='5' class='py-1.5 px-3 font-semibold text-primary-600 dark:text-primary-400'>1. DASAR UPAH & TUNJANGAN (WAGES & ALLOWANCES)</td>
                                                                         <td class='py-1.5 px-3 text-right font-bold'>Rp {$fmt($subA)}</td>
                                                                         <td class='py-1.5 px-3 text-right font-bold text-primary-600 dark:text-primary-400'>Rp {$fmt($subA * $qty)}</td>
                                                                     </tr>
                                                                     <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                         <td class='py-1.5 px-3 text-center text-slate-400'>1.1</td>
                                                                         <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>Gaji Pokok (Basic Salary)</td>
                                                                         <td class='py-1.5 px-3'>Input Gaji Pokok</td>
                                                                         <td class='py-1.5 px-3'>-</td>
                                                                         <td class='py-1.5 px-3 text-right'>-</td>
                                                                         <td class='py-1.5 px-3 text-right'>-</td>
                                                                         <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($scaled_gapok)}</td>
                                                                         <td class='py-1.5 px-3 text-right'>Rp {$fmt($scaled_gapok * $qty)}</td>
                                                                     </tr>
                                                                     <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                         <td class='py-1.5 px-3 text-center text-slate-400'>1.2</td>
                                                                         <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>Tunjangan Tetap (Fixed Allowance)</td>
                                                                         <td class='py-1.5 px-3'>Input Tunjangan Tetap</td>
                                                                         <td class='py-1.5 px-3'>-</td>
                                                                         <td class='py-1.5 px-3 text-right'>-</td>
                                                                         <td class='py-1.5 px-3 text-right'>-</td>
                                                                         <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($scaled_fixed)}</td>
                                                                         <td class='py-1.5 px-3 text-right'>Rp {$fmt($scaled_fixed * $qty)}</td>
                                                                     </tr>
                                                                     <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                         <td class='py-1.5 px-3 text-center text-slate-400'>1.3</td>
                                                                         <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>Tunjangan Tidak Tetap (Non-Fixed Allowance)</td>
                                                                         <td class='py-1.5 px-3'>Input Tunjangan Tidak Tetap</td>
                                                                         <td class='py-1.5 px-3'>-</td>
                                                                         <td class='py-1.5 px-3 text-right'>-</td>
                                                                         <td class='py-1.5 px-3 text-right'>-</td>
                                                                         <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($scaled_non_fixed)}</td>
                                                                         <td class='py-1.5 px-3 text-right'>Rp {$fmt($scaled_non_fixed * $qty)}</td>
                                                                     </tr>

                                                                     <!-- 2. Akrual Bulanan -->
                                                                     <tr class='bg-slate-50/30 dark:bg-slate-800/10 font-semibold text-slate-900 dark:text-white'>
                                                                         <td class='py-1.5 px-3 text-center'>2</td>
                                                                         <td colspan='5' class='py-1.5 px-3 font-semibold text-primary-600 dark:text-primary-400'>2. AKRUAL BULANAN (MONTHLY ACCRUALS)</td>
                                                                         <td class='py-1.5 px-3 text-right font-bold'>Rp {$fmt($subB)}</td>
                                                                         <td class='py-1.5 px-3 text-right font-bold text-primary-600 dark:text-primary-400'>Rp {$fmt($subB * $qty)}</td>
                                                                     </tr>
                                                                     <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                         <td class='py-1.5 px-3 text-center text-slate-400'>2.1</td>
                                                                         <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>Akrual THR (Religious Festive Allowance)</td>
                                                                         <td class='py-1.5 px-3'>Dasar THR: Rp {$fmt($scaled_thr_basis)}</td>
                                                                         <td class='py-1.5 px-3'>1 / 12</td>
                                                                         <td class='py-1.5 px-3 text-right'>-</td>
                                                                         <td class='py-1.5 px-3 text-right'>-</td>
                                                                         <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($scaled_thr)}</td>
                                                                         <td class='py-1.5 px-3 text-right'>Rp {$fmt($scaled_thr * $qty)}</td>
                                                                     </tr>
                                                                     <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                         <td class='py-1.5 px-3 text-center text-slate-400'>2.2</td>
                                                                         <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>Akrual Kompensasi (Contract Compensation)</td>
                                                                         <td class='py-1.5 px-3'>Dasar Komp: Rp {$fmt($scaled_thr_basis)}</td>
                                                                         <td class='py-1.5 px-3'>1 / 12</td>
                                                                         <td class='py-1.5 px-3 text-right'>-</td>
                                                                         <td class='py-1.5 px-3 text-right'>-</td>
                                                                         <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($scaled_comp)}</td>
                                                                         <td class='py-1.5 px-3 text-right'>Rp {$fmt($scaled_comp * $qty)}</td>
                                                                     </tr>

                                                                     <!-- 3. BPJS Contributions -->
                                                                     <tr class='bg-slate-50/30 dark:bg-slate-800/10 font-semibold text-slate-900 dark:text-white'>
                                                                         <td class='py-1.5 px-3 text-center'>3</td>
                                                                         <td colspan='5' class='py-1.5 px-3 font-semibold text-primary-600 dark:text-primary-400'>3. IURAN BPJS (BPJS CONTRIBUTIONS)</td>
                                                                         <td class='py-1.5 px-3 text-right font-bold'>Rp {$fmt($subC)}</td>
                                                                         <td class='py-1.5 px-3 text-right font-bold text-primary-600 dark:text-primary-400'>Rp {$fmt($subC * $qty)}</td>
                                                                     </tr>
                                                                     <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                         <td class='py-1.5 px-3 text-center text-slate-400'>3.1</td>
                                                                         <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>BPJS Kesehatan (JKN)</td>
                                                                         <td class='py-1.5 px-3'>Upah Dasar: Rp {$fmt($jkn_base)}</td>
                                                                         <td class='py-1.5 px-3'>Perusahaan: {$jkn_employer_rate_pct}%, Karyawan: {$jkn_employee_rate_pct}%</td>
                                                                         <td class='py-1.5 px-3 text-right text-slate-600 dark:text-slate-300'>Rp {$fmt($jkn_employer)}</td>
                                                                         <td class='py-1.5 px-3 text-right text-slate-400 dark:text-slate-500'>Rp {$fmt($jkn_employee)} {$borne_jkn}</td>
                                                                         <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($scaled_jkn_total)}</td>
                                                                         <td class='py-1.5 px-3 text-right'>Rp {$fmt($scaled_jkn_total * $qty)}</td>
                                                                     </tr>
                                                                     <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                         <td class='py-1.5 px-3 text-center text-slate-400'>3.2</td>
                                                                         <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>BPJS Ketenagakerjaan JKK</td>
                                                                         <td class='py-1.5 px-3'>Upah Dasar: Rp {$fmt($jkk_base)}</td>
                                                                         <td class='py-1.5 px-3'>Perusahaan: {$jkk_rate_pct}%, Karyawan: {$jkk_employee_rate_pct}%</td>
                                                                         <td class='py-1.5 px-3 text-right text-slate-600 dark:text-slate-300'>Rp {$fmt($jkk_employer)}</td>
                                                                         <td class='py-1.5 px-3 text-right text-slate-400 dark:text-slate-500'>Rp {$fmt($jkk_employee)} {$borne_jkk}</td>
                                                                         <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($jkk_total)}</td>
                                                                         <td class='py-1.5 px-3 text-right'>Rp {$fmt($jkk_total * $qty)}</td>
                                                                     </tr>
                                                                     <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                         <td class='py-1.5 px-3 text-center text-slate-400'>3.3</td>
                                                                         <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>BPJS Ketenagakerjaan JKM</td>
                                                                         <td class='py-1.5 px-3'>Upah Dasar: Rp {$fmt($jkm_base)}</td>
                                                                         <td class='py-1.5 px-3'>Perusahaan: {$jkm_rate_pct}%, Karyawan: {$jkm_employee_rate_pct}%</td>
                                                                         <td class='py-1.5 px-3 text-right text-slate-600 dark:text-slate-300'>Rp {$fmt($jkm_employer)}</td>
                                                                         <td class='py-1.5 px-3 text-right text-slate-400 dark:text-slate-500'>Rp {$fmt($jkm_employee)} {$borne_jkm}</td>
                                                                         <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($jkm_total)}</td>
                                                                         <td class='py-1.5 px-3 text-right'>Rp {$fmt($jkm_total * $qty)}</td>
                                                                     </tr>
                                                                     <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                         <td class='py-1.5 px-3 text-center text-slate-400'>3.4</td>
                                                                         <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>BPJS Ketenagakerjaan JHT</td>
                                                                         <td class='py-1.5 px-3'>Upah Dasar: Rp {$fmt($jht_base)}</td>
                                                                         <td class='py-1.5 px-3'>Perusahaan: {$jht_employer_rate_pct}%, Karyawan: {$jht_employee_rate_pct}%</td>
                                                                         <td class='py-1.5 px-3 text-right text-slate-600 dark:text-slate-300'>Rp {$fmt($jht_employer)}</td>
                                                                         <td class='py-1.5 px-3 text-right text-slate-400 dark:text-slate-500'>Rp {$fmt($jht_employee)} {$borne_jht}</td>
                                                                         <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($jht_total)}</td>
                                                                         <td class='py-1.5 px-3 text-right'>Rp {$fmt($jht_total * $qty)}</td>
                                                                     </tr>
                                                                     <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                         <td class='py-1.5 px-3 text-center text-slate-400'>3.5</td>
                                                                         <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>BPJS Ketenagakerjaan JP</td>
                                                                         <td class='py-1.5 px-3'>Upah Dasar: Rp {$fmt($jp_base)}</td>
                                                                         <td class='py-1.5 px-3'>Perusahaan: {$jp_employer_rate_pct}%, Karyawan: {$jp_employee_rate_pct}%</td>
                                                                         <td class='py-1.5 px-3 text-right text-slate-600 dark:text-slate-300'>Rp {$fmt($jp_employer)}</td>
                                                                         <td class='py-1.5 px-3 text-right text-slate-400 dark:text-slate-500'>Rp {$fmt($jp_employee)} {$borne_jp}</td>
                                                                         <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($jp_total)}</td>
                                                                         <td class='py-1.5 px-3 text-right'>Rp {$fmt($jp_total * $qty)}</td>
                                                                     </tr>

                                                                     <!-- 4. Pajak PPh 21 -->
                                                                     <tr class='bg-slate-50/30 dark:bg-slate-800/10 font-semibold text-slate-900 dark:text-white'>
                                                                         <td class='py-1.5 px-3 text-center'>4</td>
                                                                         <td colspan='5' class='py-1.5 px-3 font-semibold text-primary-600 dark:text-primary-400'>4. PAJAK PPH 21 (INCOME TAX)</td>
                                                                         <td class='py-1.5 px-3 text-right font-bold'>Rp {$fmt($subD)}</td>
                                                                         <td class='py-1.5 px-3 text-right font-bold text-primary-600 dark:text-primary-400'>Rp {$fmt($subD * $qty)}</td>
                                                                     </tr>
                                                                     <tr class='hover:bg-slate-50/50 dark:hover:bg-slate-800/30'>
                                                                         <td class='py-1.5 px-3 text-center text-slate-400'>4.1</td>
                                                                         <td class='py-1.5 px-3 font-medium text-slate-800 dark:text-slate-200'>Pajak PPh 21 Bulanan</td>
                                                                         <td class='py-1.5 px-3'>Bruto: Rp {$fmt($scaled_bruto)} | Status: {$borne_tax}</td>
                                                                         <td class='py-1.5 px-3'>Metode: {$tax_method} | Tarif: {$tax_rate_pct}%</td>
                                                                         <td class='py-1.5 px-3 text-right'>-</td>
                                                                         <td class='py-1.5 px-3 text-right'>-</td>
                                                                         <td class='py-1.5 px-3 text-right font-medium'>Rp {$fmt($scaled_tax)}</td>
                                                                         <td class='py-1.5 px-3 text-right'>Rp {$fmt($scaled_tax * $qty)}</td>
                                                                     </tr>

                                                                     <!-- 5. Biaya Ekstra -->
                                                                     {$extra_costs_rows_html}

                                                                     <!-- Grand Total -->
                                                                     <tr class='bg-primary-50 dark:bg-primary-950/20 text-slate-900 dark:text-white border-t-2 border-primary-500 font-bold text-[11px]'>
                                                                         <td class='py-2 px-3 text-center'>TOTAL</td>
                                                                         <td colspan='5' class='py-2 px-3 font-bold text-primary-700 dark:text-primary-400 uppercase tracking-wider text-[10px]'>TOTAL DIRECT MANPOWER COST</td>
                                                                         <td class='py-2 px-3 text-right text-sm text-primary-700 dark:text-primary-400'>Rp {$fmt($unitCost)}</td>
                                                                         <td class='py-2 px-3 text-right text-sm text-primary-700 dark:text-primary-400'>Rp {$fmt($lineTotal)}</td>
                                                                     </tr>
                                                                 </tbody>
                                                             </table>
                                                         </div>
                                                     </details>
                                                 </td>
                                             </tr>
                                        ";
                                    }
                                }

                                return new HtmlString("
                                    <div class='relative overflow-x-auto shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700'>
                                        <table class='w-full text-[11px] text-left text-gray-500 dark:text-gray-400'>
                                            <thead class='text-[10px] text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400'>
                                                <tr>
                                                    <th scope='col' class='px-2 py-3'>".__('Position')."</th>
                                                    <th scope='col' class='px-2 py-3 text-center'>".__('Qty')."</th>
                                                    <th scope='col' class='px-2 py-3 text-right'>".__('Wage')."</th>
                                                    <th scope='col' class='px-2 py-3 text-right'>".__('Accruals')."</th>
                                                    <th scope='col' class='px-2 py-3 text-right'>".__('BPJS')."</th>
                                                    <th scope='col' class='px-2 py-3 text-right'>".__('PPh 21')."</th>
                                                    <th scope='col' class='px-2 py-3 text-right'>".__('Extra')."</th>
                                                    <th scope='col' class='px-2 py-3 text-right bg-blue-50/50 dark:bg-blue-900/10 text-primary-600'>".__('Total/Pax')."</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {$tableContent}
                                            </tbody>
                                            <tfoot>
                                                <tr class='font-bold text-gray-900 dark:text-white bg-gray-100/50 dark:bg-gray-800/50'>
                                                    <td colspan='7' class='px-2 py-4 text-right uppercase tracking-wider text-[10px]'>".__('Total Estimated Monthly Cost')."</td>
                                                    <td class='px-2 py-4 text-right text-sm text-primary-600'>Rp ".number_format($totalTemplateCost, 0, ',', '.')."</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class='mt-4 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800'>
                                        <div class='flex gap-2 text-xs text-blue-700 dark:text-blue-300'>
                                            <span class='font-bold uppercase shrink-0'>".__('Information:')."</span>
                                            <ul class='list-disc list-inside space-y-1 opacity-80'>
                                                <li>".__('Calculations follow BPJS PPU and latest JKK risk categories.').'</li>
                                                <li>'.__('PPh 21 Tax supports both TER and Progressive (Pasal 17) methods.').'</li>
                                                <li>'.__('Extra costs are monthly accruals from annual budgets.').'</li>
                                                <li>'.__('Policies can be defined at Cluster level or overridden per Personnel.').'</li>
                                            </ul>
                                        </div>
                                    </div>
                                ');
                            }),
                        Hidden::make('simulation_trigger'),
                    ]),
            ])->columnSpanFull(),
        ];
    }
}
