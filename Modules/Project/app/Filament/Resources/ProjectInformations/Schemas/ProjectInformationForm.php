<?php

namespace Modules\Project\Filament\Resources\ProjectInformations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

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

                                // PIC Section
                                TextInput::make('pic_client_name')->label('Nama Klien (PIC)'),
                                TextInput::make('pic_client_phone')->label('Nomor Telp (PIC)')->tel(),
                                TextInput::make('pic_finance_name')->label('Nama Keuangan (PIC)'),
                                TextInput::make('pic_finance_phone')->label('No. Telp Keuangan (PIC)')->tel(),
                                TextInput::make('pic_finance_email')->label('Email Keuangan (PIC)')->email(),

                                // Operational & Financial Details
                                TextInput::make('operational_visit_schedule')->label('Jadwal Kunjungan Operasional'),
                                DatePicker::make('bapp_cut_off_date')->label('Tanggal Cut Off BAPP')->native(false),
                                DatePicker::make('process_date')->label('Tanggal Proses Verifikasi BAPP')->native(false),
                                DatePicker::make('max_invoice_send_date')->label('Tanggal Maks. Pengiriman Invoice')->native(false),

                                TextInput::make('direct_cost')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->label('Nilai Direct Cost/month'),
                                TextInput::make('revenue_per_month')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->label('Nilai Revenue / bulan'),
                                TextInput::make('management_fee_per_month')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->label('Management Fee / month'),
                                TextInput::make('ppn_percentage')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(11)
                                    ->label('PPN (%)'),

                                Select::make('payment_term_id')->relationship('paymentTerm', 'name')->label('ToP')->searchable()->preload(),
                                Select::make('project_type_id')->relationship('projectType', 'name')->searchable()->preload(),
                                Select::make('billing_option_id')->relationship('billingOption', 'name')->searchable()->preload(),
                                Select::make('oprep_id')->relationship('oprep', 'name')->label('Nama Oprep')->searchable()->preload(),
                                Select::make('ams_id')->relationship('ams', 'name')->label('Nama AMS')->searchable()->preload(),

                                Textarea::make('description')->columnSpanFull()->rows(3),
                                Textarea::make('remarks')->columnSpanFull()->rows(2),
                            ])
                            ->columns(2),
                        Tab::make('Materials & Manpower')
                            ->schema([
                                // Manpower Section
                                TextInput::make('manpower_cleaner')->numeric()->label('Cleaner')->default(0),
                                TextInput::make('manpower_leader_cleaner')->numeric()->label('Leader Cleaner')->default(0),
                                TextInput::make('manpower_engineer')->numeric()->label('Engineer')->default(0),
                                TextInput::make('manpower_security')->numeric()->label('Security')->default(0),

                                // Materials & Equipment (JSON)
                                Repeater::make('material_equipment_details')
                                    ->schema([
                                        Select::make('item_id')
                                            ->label('Item')
                                            ->options(\Modules\MasterData\Models\Item::pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->preload(),
                                        TextInput::make('specification')->placeholder('e.g., Laki-laki, 3 layer'),
                                        TextInput::make('quantity')->numeric()->default(1),
                                        TextInput::make('notes'),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->addActionLabel('Add Material/Equipment'),

                                // Risk & Feasibility
                                Repeater::make('risk_management')
                                    ->schema([
                                        TextInput::make('risk_item')->required(),
                                        TextInput::make('mitigation')->required(),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->addActionLabel('Add Risk Item'),
                                Repeater::make('feasibility_study')
                                    ->schema([
                                        TextInput::make('item')->required(),
                                        TextInput::make('value')->required(),
                                        Textarea::make('notes')->rows(2),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->addActionLabel('Add Feasibility Item'),
                            ])
                            ->columns(2),
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
}
