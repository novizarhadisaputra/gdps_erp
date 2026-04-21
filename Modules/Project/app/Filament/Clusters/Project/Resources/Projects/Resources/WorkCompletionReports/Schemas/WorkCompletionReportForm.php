<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\SalesOrder;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Models\Project;

class WorkCompletionReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Documents')
                    ->description('Unduh draft BAPP untuk ditandatangani, lalu unggah kembali hasil pindaian (Scan) dokumen yang telah ditandatangani untuk memproses persetujuan.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('draft_report')
                                    ->label('Draft BAPP (Unsigned)')
                                    ->collection('draft_report')
                                    ->disk('s3')
                                    ->downloadable()
                                    ->openable()
                                    ->helperText('Dokumen draf hasil sistem yang belum ditandatangani.'),

                                SpatieMediaLibraryFileUpload::make('signed_report')
                                    ->label('Signed BAPP (Final Scan)')
                                    ->collection('signed_report')
                                    ->disk('s3')
                                    ->downloadable()
                                    ->openable()
                                    ->helperText('Unggah pindaian dokumen yang telah ditandatangani oleh kedua belah pihak.')
                                    ->required(fn ($get) => $get('status') === WorkCompletionStatus::Submitted->value),
                            ]),
                    ])->columnSpanFull(),

                Section::make('Report Details')
                    ->schema([
                        TextInput::make('report_number')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated'),
                        DatePicker::make('document_date')
                            ->required()
                            ->default(now()),
                        Select::make('project_id')
                            ->label('Project')
                            ->options(Project::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])->columns(2),

                Section::make('Service & Progress')
                    ->schema([
                        DatePicker::make('service_period_start')
                            ->required(),
                        DatePicker::make('service_period_end')
                            ->required(),
                        TextInput::make('work_progress_percentage')
                            ->numeric()
                            ->suffix('%')
                            ->required()
                            ->default(100),
                        Select::make('status')
                            ->options(WorkCompletionStatus::class)
                            ->required()
                            ->default(WorkCompletionStatus::Draft),
                    ])->columns(2),

                Section::make('Additional Information')
                    ->schema([
                        Select::make('sales_order_id')
                            ->label('Sales Order')
                            ->options(SalesOrder::all()->pluck('so_number', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if (!$state) return;
                                
                                $so = SalesOrder::with(['proposal.profitabilityAnalysis'])->find($state);
                                if ($so && $so->proposal && $so->proposal->profitabilityAnalysis) {
                                    $pa = $so->proposal->profitabilityAnalysis;
                                    
                                    $manpower = $pa->manpower_requirements ?? [];
                                    $operational = $pa->financial_assumptions['operational_costs'] ?? [];
                                    
                                    $items = [];
                                    
                                    foreach ($manpower as $mp) {
                                        $items[] = [
                                            'item_name' => $mp['job_position_name'] ?? 'Personnel',
                                            'quantity' => $mp['quantity'] ?? 0,
                                            'uom' => $mp['uom'] ?? 'Person',
                                            'unit_price' => $mp['unit_cost'] ?? 0,
                                            'total_price' => $mp['total_monthly_cost'] ?? 0,
                                            'so_reference' => $so->so_number,
                                        ];
                                    }
                                    
                                    foreach ($operational as $op) {
                                        $items[] = [
                                            'item_name' => $op['item_name'] ?? 'Item',
                                            'quantity' => $op['quantity'] ?? 0,
                                            'uom' => $op['uom'] ?? 'Unit',
                                            'unit_price' => $op['unit_cost'] ?? 0,
                                            'total_price' => $op['total_monthly_cost'] ?? 0,
                                            'so_reference' => $so->so_number,
                                        ];
                                    }
                                    
                                    $set('items', $items);
                                    
                                    // Also sync customer from SO
                                    if ($so->customer_id) {
                                        $set('customer_id', $so->customer_id);
                                    }
                                }
                            }),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Standardized Work Completion Table')
                    ->description('Detail rincian pekerjaan yang diselesaikan berdasarkan Sales Order.')
                    ->schema([
                        Repeater::make('items')
                            ->label('Line Items')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        TextInput::make('item_name')
                                            ->label('Item')
                                            ->required()
                                            ->columnSpan(2),
                                        TextInput::make('quantity')
                                            ->label('Quantity')
                                            ->numeric()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn ($get, $set) => $set('total_price', floatval($get('quantity') ?? 0) * floatval($get('unit_price') ?? 0))),
                                        TextInput::make('uom')
                                            ->label('Unit')
                                            ->required(),
                                        TextInput::make('unit_price')
                                            ->label('Price / Unit')
                                            ->numeric()
                                            ->prefix('IDR')
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn ($get, $set) => $set('total_price', floatval($get('quantity') ?? 0) * floatval($get('unit_price') ?? 0))),
                                        TextInput::make('total_price')
                                            ->label('Total Price')
                                            ->numeric()
                                            ->prefix('IDR')
                                            ->readonly()
                                            ->columnSpan(1),
                                        TextInput::make('so_reference')
                                            ->label('SO Ref')
                                            ->disabled()
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->columnSpanFull()
                            ->reorderable()
                            ->addActionLabel('Add Manual Entry'),
                    ]),
            ]);
    }
}
