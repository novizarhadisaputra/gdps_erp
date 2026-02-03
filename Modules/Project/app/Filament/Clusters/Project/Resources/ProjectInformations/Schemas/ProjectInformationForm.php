<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\ProjectInformations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BillingOptions\Schemas\BillingOptionForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees\Schemas\EmployeeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\Schemas\ItemForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Schemas\PaymentTermForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectTypes\Schemas\ProjectTypeForm;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\ItemCategory;

class ProjectInformationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->relationship('project', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                Tabs::make('Operational Information')
                    ->tabs([
                        Tab::make('Operational & Financial')
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        'planning' => 'Planning',
                                        'active' => 'Active',
                                        'completed' => 'Completed',
                                        'on hold' => 'On Hold',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->required()
                                    ->default('planning'),
                                TextInput::make('previous_code')
                                    ->label('Sebelum Revisi'),
                                DatePicker::make('start_date')->native(false),
                                DatePicker::make('end_date')->after('start_date')->native(false),

                                // Operational & Financial Details
                                TextInput::make('operational_visit_schedule')->label('Jadwal Kunjungan Operasional'),
                                DatePicker::make('bapp_cut_off_date')->label('Tanggal Cut Off BAPP')->native(false),
                                DatePicker::make('process_date')->label('Tanggal Proses Verifikasi BAPP')->native(false),
                                DatePicker::make('max_invoice_send_date')->label('Tanggal Maks. Pengiriman Invoice')->native(false),

                                TextInput::make('direct_cost')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->label('Nilai Direct Cost/month')
                                    ->helperText('Contoh: 80,000,000'),
                                TextInput::make('revenue_per_month')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->label('Nilai Revenue / bulan')
                                    ->helperText('Contoh: 100,000,000'),
                                TextInput::make('management_fee_per_month')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->label('Management Fee / month'),
                                TextInput::make('ppn_percentage')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(11)
                                    ->label('PPN (%)'),

                                Select::make('payment_term_id')->relationship('paymentTerm', 'name')->label('ToP')->searchable()->preload()->createOptionForm(PaymentTermForm::schema()),
                                Select::make('project_type_id')->relationship('projectType', 'name')->searchable()->preload()->createOptionForm(ProjectTypeForm::schema()),
                                Select::make('billing_option_id')->relationship('billingOption', 'name')->searchable()->preload()->createOptionForm(BillingOptionForm::schema()),
                                Select::make('oprep_id')->relationship('oprep', 'name')->label('Nama Oprep')->searchable()->preload()->createOptionForm(EmployeeForm::schema()),
                                Select::make('ams_id')->relationship('ams', 'name')->label('Nama AMS')->searchable()->preload()->createOptionForm(EmployeeForm::schema()),

                                Textarea::make('description')->columnSpanFull()->rows(3),
                                Textarea::make('remarks')->columnSpanFull()->rows(2),
                            ])
                            ->columns(2),
                        Tab::make('Materials & Manpower')
                            ->schema(function () {
                                $schemas = [];
                                $categories = ItemCategory::where('is_active', true)->get();

                                foreach ($categories as $category) {
                                    $isManpower = $category->name === 'Manpower';
                                    $schemas[] = Repeater::make("analysis_details.{$category->id}")
                                        ->label($category->name.' Details')
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
                                                ->required(),
                                            TextInput::make('quantity')
                                                ->label($isManpower ? 'Count' : 'Quantity')
                                                ->numeric()->default(1)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                                            TextInput::make('price')
                                                ->label($isManpower ? 'Salary' : 'Price')
                                                ->numeric()->prefix('IDR')
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                                            TextInput::make('notes'),
                                        ])->columns(4)
                                        ->addActionLabel("Add {$category->name}");
                                }

                                return $schemas;
                            }),
                        Tab::make('Remuneration')
                            ->schema([
                                DatePicker::make('payroll_date')->label('Tanggal Penggajian TAD')->native(false),
                                DatePicker::make('overtime_cut_off_date')->label('Tanggal Cut Off Lembur')->native(false),
                                Select::make('ipk_status')
                                    ->options([
                                        'ACCRUE' => 'ACCRUE',
                                        'REIMBURSE' => 'REIMBURSE',
                                        'DITAGIHKAN TERPISAH' => 'DITAGIHKAN TERPISAH',
                                    ])->label('Accrued - IPK'),
                                Select::make('thr_status')
                                    ->options([
                                        'ACCRUE' => 'ACCRUE',
                                        'REIMBURSE' => 'REIMBURSE',
                                        'DITAGIHKAN TERPISAH' => 'DITAGIHKAN TERPISAH',
                                    ])->label('Accrued - THR'),

                                Repeater::make('remuneration_details')
                                    ->schema([
                                        TextInput::make('component_name')->required()->placeholder('e.g., Gaji Pokok, Tunjangan Shift'),
                                        TextInput::make('amount')->numeric()->prefix('IDR')->required(),
                                        TextInput::make('notes'),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->addActionLabel('Add Remuneration Component'),

                                Repeater::make('profitability_analysis')
                                    ->schema([
                                        TextInput::make('item')->required(),
                                        TextInput::make('amount')->numeric()->required()->prefix('IDR'),
                                        Textarea::make('notes')->rows(2),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->addActionLabel('Add Profitability Item'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function calculateDirectCost($get, $set): void
    {
        $analysisDetails = $get('analysis_details') ?? [];
        $totalDirectCost = 0;

        foreach ($analysisDetails as $categoryId => $items) {
            $totalDirectCost += collect($items)->reduce(function ($carry, $item) {
                return $carry + (($item['quantity'] ?? 0) * ($item['price'] ?? 0));
            }, 0);
        }

        $set('direct_cost', $totalDirectCost);
    }
}
