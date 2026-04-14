<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Enums\SalesOrderStatus;
use Modules\CRM\Enums\SalesOrderType;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Illuminate\Support\HtmlString;
use Modules\Project\Models\Project;

class SalesOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('General Information')
                        ->description('Informasi dasar project dan dokumen referensi.')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('so_number')
                                        ->label('SO Number')
                                        ->placeholder('e.g. GDPS/UB/SO-001/26')
                                        ->helperText('Nomor SO digenerate otomatis oleh sistem.')
                                        ->hidden(fn (string $operation): bool => $operation === 'create')
                                        ->disabled()
                                        ->unique(ignoreRecord: true),
                                    DatePicker::make('order_date')
                                        ->required()
                                        ->placeholder('Pilih tanggal order')
                                        ->default(now()),
                                    Select::make('project_id')
                                        ->relationship('project', 'code')
                                        ->placeholder('Cari atau pilih project...')
                                        ->helperText('Hubungkan dengan project untuk menarik data RAB otomatis.')
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if (! $state) {
                                                return;
                                            }
                                            $project = Project::find($state);
                                            if ($project) {
                                                $set('customer_id', $project->customer_id);

                                                $proposal = $project->lead?->proposals()->where('status', ProposalStatus::Approved)->first();
                                                if ($proposal) {
                                                    $set('proposal_id', $proposal->id);
                                                    $set('amount', $proposal->amount);
                                                }

                                                $analysis = $project->profitabilityAnalysis;
                                                if ($analysis) {
                                                    $set('manpower_initial_qty', $analysis->total_manpower);
                                                    $set('management_fee_percentage', $analysis->management_fee_rate);
                                                    $set('tax_percentage', $analysis->tax_rate ?? 11);

                                                    // Auto-fill content_config from PA if empty
                                                    $manpower = $analysis->manpower_requirements;
                                                    $financials = $analysis->financial_assumptions;

                                                    $items = collect($financials['operational_costs'] ?? [])->map(fn ($item) => [
                                                        'description' => $item['item_name'],
                                                        'uom' => 'Unit',
                                                        'quantity' => $item['quantity'],
                                                        'unit_price' => $item['unit_cost'],
                                                        'total_price' => $item['total_monthly_cost'],
                                                    ])->toArray();

                                                    $set('content_config.items', $items);
                                                    $set('content_config.manpower_details', $manpower);
                                                }

                                                $lead = $project->lead;
                                                if ($lead) {
                                                    $set('sales_pic_id', $lead->ams_id);
                                                    $set('project_manager_id', $lead->oprep_id);
                                                    if (isset($analysis)) {
                                                        $set('service_type', $analysis->productCluster?->name ?? $lead->service_type);
                                                        $set('job_location', $analysis->projectArea?->name ?? $lead->job_location);
                                                    } else {
                                                        $set('service_type', $lead->service_type);
                                                        $set('job_location', $lead->job_location);
                                                    }
                                                }
                                            }
                                        }),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    Select::make('customer_id')
                                        ->relationship('customer', 'name')
                                        ->placeholder('Customer otomatis terisi...')
                                        ->required()
                                        ->disabled()
                                        ->dehydrated(),
                                    Select::make('proposal_id')
                                        ->relationship('proposal', 'proposal_number')
                                        ->placeholder('Proposal otomatis terisi...')
                                        ->required()
                                        ->disabled()
                                        ->dehydrated(),
                                ]),
                            Grid::make(3)
                                ->schema([
                                    Select::make('type')
                                        ->options(SalesOrderType::class)
                                        ->default(SalesOrderType::External)
                                        ->required(),
                                    Select::make('status')
                                        ->options(SalesOrderStatus::class)
                                        ->required()
                                        ->default(SalesOrderStatus::Draft)
                                        ->disabled()
                                        ->dehydrated(),
                                ]),
                        ]),
                    Step::make('Execution & Staffing')
                        ->description('Personil dan lokasi pelaksanaan kerja.')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('sales_pic_id')
                                        ->label('Sales PIC (AMS)')
                                        ->placeholder('Pilih Sales Account Management...')
                                        ->relationship('salesPic', 'name')
                                        ->searchable(),
                                    Select::make('project_manager_id')
                                        ->label('Project Manager (Oprep)')
                                        ->placeholder('Pilih Project Manager...')
                                        ->relationship('projectManager', 'name')
                                        ->searchable(),
                                    TextInput::make('service_type')
                                        ->placeholder('e.g. Manpower Supply, Cleaning Service')
                                        ->helperText('Jenis layanan utama yang diberikan.'),
                                    TextInput::make('job_location')
                                        ->placeholder('e.g. Bandara Soekarno-Hatta')
                                        ->helperText('Lokasi spesifik pengerjaan project.'),
                                    TextInput::make('manpower_initial_qty')
                                        ->label('Initial Manpower Qty')
                                        ->numeric()
                                        ->placeholder('0')
                                        ->helperText('Jumlah personil awal sesuai kontrak.')
                                        ->default(0),
                                    KeyValue::make('manpower_composition')
                                        ->label('Ringkasan Komposisi')
                                        ->helperText('Catatan tambahan untuk komposisi personil.')
                                        ->columnSpanFull(),
                                    \Filament\Forms\Components\Placeholder::make('manpower_attachment_preview')
                                        ->label('Backup Costing Manpower')
                                        ->columnSpanFull()
                                        ->visible(fn (Get $get) => ($pa = ProfitabilityAnalysis::find($get('profitability_analysis_id'))) && (bool) $pa->is_manual_cost)
                                        ->content(function (Get $get) {
                                            $pa = ProfitabilityAnalysis::find($get('profitability_analysis_id'));
                                            if (! $pa) {
                                                return 'Data PA tidak ditemukan.';
                                            }
                                            $media = $pa->getFirstMedia('manpower_costing_backup');

                                            return $media 
                                                ? new HtmlString("<a href='{$media->getUrl()}' target='_blank' class='text-primary-600 font-bold underline flex items-center gap-1'><x-heroicon-o-arrow-down-tray class='w-4 h-4'/> Download Original Manpower Costing ({$media->file_name})</a>")
                                                : 'Tidak ada lampiran manpower.';
                                        }),
                                ]),
                        ]),
                    Step::make('Item Details')
                        ->description('Data rincian biaya dan personil yang ditarik dari Profitability Analysis.')
                        ->schema([
                            Section::make('Source Documents (Manual Mode)')
                                ->description('File perhitungan manual yang diunggah ke Profitability Analysis.')
                                ->visible(fn (Get $get) => ($pa = ProfitabilityAnalysis::find($get('profitability_analysis_id'))) && (bool) $pa->is_manual_cost)
                                ->schema([
                                    \Filament\Forms\Components\Placeholder::make('operational_attachment_preview')
                                        ->label('Backup Costing Tools & Equipment')
                                        ->content(function (Get $get) {
                                            $pa = ProfitabilityAnalysis::find($get('profitability_analysis_id'));
                                            if (! $pa) {
                                                return 'Data PA tidak ditemukan.';
                                            }
                                            $media = $pa->getFirstMedia('operational_costing_backup');

                                            return $media 
                                                ? new HtmlString("<a href='{$media->getUrl()}' target='_blank' class='text-primary-600 font-bold underline flex items-center gap-1'><x-heroicon-o-arrow-down-tray class='w-4 h-4'/> Download Original Tools costing ({$media->file_name})</a>")
                                                : 'Tidak ada lampiran tools/equipment.';
                                        }),
                                ]),
                            Section::make('Service Items (RAB)')
                                ->description('Rincian komponen biaya layanan. Field ini terisi otomatis dari PA.')
                                ->visible(fn (Get $get) => ($pa = ProfitabilityAnalysis::find($get('profitability_analysis_id'))) && ! (bool) $pa->is_manual_cost)
                                ->schema([
                                    Repeater::make('content_config.items')
                                        ->label('Items Details')
                                        ->schema([
                                            TextInput::make('description')->placeholder('Item description')->required(),
                                            TextInput::make('uom')->label('UoM')->placeholder('e.g. Unit, Org, Lot')->default('Unit'),
                                            TextInput::make('quantity')->numeric()->placeholder('1')->required(),
                                            TextInput::make('unit_price')->numeric()->placeholder('0')->prefix('IDR')->required(),
                                            TextInput::make('total_price')->numeric()->placeholder('0')->prefix('IDR')->required(),
                                        ])
                                        ->columns(5)
                                        ->reorderableWithButtons(),
                                ]),
                            Section::make('Manpower Composition')
                                ->description('Rincian jumlah personil per jabatan.')
                                ->visible(fn (Get $get) => ($pa = ProfitabilityAnalysis::find($get('profitability_analysis_id'))) && ! (bool) $pa->is_manual_cost)
                                ->schema([
                                    Repeater::make('content_config.manpower_details')
                                        ->label('Staffing Details')
                                        ->schema([
                                            TextInput::make('job_position_name')->label('Position')->placeholder('e.g. Supervisor')->required(),
                                            TextInput::make('quantity')->numeric()->placeholder('1')->required(),
                                            TextInput::make('unit_cost')->numeric()->placeholder('0')->prefix('IDR'),
                                            TextInput::make('total_monthly_cost')->numeric()->placeholder('0')->prefix('IDR'),
                                        ])
                                        ->columns(4),
                                ]),
                        ]),
                    Step::make('Financials & Terms')
                        ->description('Nilai kontrak akhir dan ketentuan pengunggahan dokumen.')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('amount')
                                        ->label('Grand Total / Month')
                                        ->placeholder('0.00')
                                        ->helperText('Nilai pendapatan kotor per bulan.')
                                        ->numeric()
                                        ->prefix('IDR')
                                        ->required(),
                                    TextInput::make('management_fee_percentage')
                                        ->label('Mgt. Fee')
                                        ->numeric()
                                        ->placeholder('10')
                                        ->suffix('%')
                                        ->default(10),
                                    TextInput::make('tax_percentage')
                                        ->label('Tax (VAT)')
                                        ->numeric()
                                        ->placeholder('11')
                                        ->suffix('%')
                                        ->default(11),
                                ]),
                            Section::make('Contractual Terms')
                                ->description('Ketentuan administrasi dan SLA penggantian personil.')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Textarea::make('payment_terms')
                                                ->label('Terms of Payment')
                                                ->placeholder('e.g. Monthly, 30 Days TOP after invoice')
                                                ->helperText('Sistem pembayaran yang disepakati.')
                                                ->rows(3),
                                            TextInput::make('probation_period')
                                                ->placeholder('e.g. 3 Months')
                                                ->default('3 Months'),
                                            TextInput::make('replacement_sla')
                                                ->label('Replacement SLA')
                                                ->placeholder('e.g. 3 Working Days')
                                                ->default('3 Working Days'),
                                            TextInput::make('reporting_schedule')
                                                ->placeholder('e.g. Every 25th of month')
                                                ->default('5th of each month'),
                                        ]),
                                ]),
                            Section::make('Official Document Upload')
                                ->description('Unggah dokumen SO fisik yang sudah ditandatangani.')
                                ->schema([
                                    SpatieMediaLibraryFileUpload::make('signed_so')
                                        ->collection('signed_so')
                                        ->label('Signed SO (Scan)')
                                        ->placeholder('Klik atau seret file ke sini...')
                                        ->required()
                                        ->helperText('Format PDF atau Image (Maks 10MB).'),
                                ]),
                        ]),
                ])
                    ->disabled(fn ($record) => $record && $record->status !== SalesOrderStatus::Draft)
                    ->columnSpanFull(),
            ]);
    }
}
