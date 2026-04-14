<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\SalesOrderAmendmentStatus;

class AmendmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Amendment Metadata')
                    ->description('Informasi dasar mengenai amandemen ini. Data ini bersifat historis dan tidak dapat diubah.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('amendment_number')
                                    ->label('No. Amandemen')
                                    ->helperText('Nomor urut perubahan kontrak.')
                                    ->disabled(),
                                DatePicker::make('amendment_date')
                                    ->label('Tanggal Amandemen')
                                    ->helperText('Tanggal disetujuinya revisi PA.')
                                    ->disabled(),
                                Select::make('status')
                                    ->label('Status')
                                    ->options(SalesOrderAmendmentStatus::class)
                                    ->badge()
                                    ->disabled(),
                            ]),
                        Textarea::make('reason')
                            ->label('Alasan Perubahan')
                            ->placeholder('Jelaskan alasan dilakukannya amandemen ini...')
                            ->helperText('Alasan yang dimasukkan saat pengajuan revisi PA.')
                            ->disabled(),
                    ]),
                
                Section::make('Comparison: Manpower & Pricing')
                    ->description('Perbandingan rinci antara data kontrak asli (Before) dan data kontrak baru hasil revisi (After). Gunakan ini untuk memverifikasi perubahan nilai atau jumlah personil.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Section::make('BEFORE (Original)')
                                    ->description('Data dari Sales Order atau amandemen sebelumnya.')
                                    ->schema([
                                        Repeater::make('before_snapshot.items')
                                            ->label('Original Items')
                                            ->schema([
                                                TextInput::make('description')->placeholder('Item description'),
                                                TextInput::make('total_price')
                                                    ->label('Total/Month')
                                                    ->numeric()
                                                    ->prefix('IDR'),
                                            ])->disabled()->dehydrated(false),
                                        
                                        Repeater::make('before_snapshot.manpower_details')
                                            ->label('Original Manpower')
                                            ->schema([
                                                TextInput::make('job_position_name')->label('Position'),
                                                TextInput::make('quantity')->label('Qty')->numeric(),
                                            ])->disabled()->dehydrated(false),
                                    ]),
                                
                                Section::make('AFTER (Revised)')
                                    ->description('Data baru yang ditarik dari Profitability Analysis hasil revisi.')
                                    ->schema([
                                        Repeater::make('after_snapshot.items')
                                            ->label('Revised Items')
                                            ->schema([
                                                TextInput::make('description')->placeholder('New item description'),
                                                TextInput::make('total_price')
                                                    ->label('Total/Month')
                                                    ->numeric()
                                                    ->prefix('IDR'),
                                            ])->disabled()->dehydrated(false),
                                        
                                        Repeater::make('after_snapshot.manpower_details')
                                            ->label('Revised Manpower')
                                            ->schema([
                                                TextInput::make('job_position_name')->label('Position'),
                                                TextInput::make('quantity')->label('Qty')->numeric(),
                                            ])->disabled()->dehydrated(false),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
