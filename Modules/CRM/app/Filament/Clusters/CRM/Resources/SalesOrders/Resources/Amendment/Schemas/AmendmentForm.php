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
                            ->label('Reason for Change')
                            ->placeholder('Describe why this amendment is being made...')
                            ->helperText('This justification will appear in the amendment document.')
                            ->disabled(fn ($record) => $record?->status !== SalesOrderAmendmentStatus::Draft)
                            ->required(),
                    ]),
                
                Section::make('Comparison: Manpower & Pricing')
                    ->description('Perbandingan rinci antara data kontrak asli (Before) dan data kontrak baru hasil revisi (After). Gunakan ini untuk memverifikasi perubahan nilai atau jumlah personil.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Section::make('Before (Original)')
                                    ->description('Data from the active Sales Order or previous amendment.')
                                    ->schema([
                                        Repeater::make('before_snapshot.items')
                                            ->label('Original Items')
                                            ->schema([
                                                TextInput::make('description')->label('Service Description'),
                                                TextInput::make('total_price')
                                                    ->label('Total / Month')
                                                    ->numeric()
                                                    ->prefix('IDR'),
                                            ])->disabled()->dehydrated(false),
                                        
                                        Repeater::make('before_snapshot.manpower_details')
                                            ->label('Original Personnel Composition')
                                            ->schema([
                                                TextInput::make('job_position_name')->label('Job Position / Rank'),
                                                TextInput::make('quantity')->label('Qty')->numeric(),
                                            ])->disabled()->dehydrated(false),
                                    ]),
                                
                                Section::make('After (Revised)')
                                    ->description('Enter the new/revised data for this amendment. This will update the parent Sales Order once approved.')
                                    ->schema([
                                        Repeater::make('after_snapshot.items')
                                            ->label('Revised Items')
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        TextInput::make('description')
                                                            ->label('Service Description')
                                                            ->required()
                                                            ->columnSpan(1),
                                                        TextInput::make('uom')
                                                            ->label('Unit')
                                                            ->default('Unit')
                                                            ->required(),
                                                        TextInput::make('quantity')
                                                            ->label('Qty')
                                                            ->numeric()
                                                            ->required(),
                                                        TextInput::make('unit_price')
                                                            ->label('Unit Price')
                                                            ->numeric()
                                                            ->prefix('IDR')
                                                            ->required()
                                                            ->live()
                                                            ->afterStateUpdated(function ($state, $get, $set) {
                                                                $qty = $get('quantity') ?? 0;
                                                                $set('total_price', $qty * $state);
                                                            }),
                                                        TextInput::make('total_price')
                                                            ->label('Total / Month')
                                                            ->numeric()
                                                            ->prefix('IDR')
                                                            ->required()
                                                            ->readOnly(),
                                                        TextInput::make('note')
                                                            ->label('Notes / Remark')
                                                            ->placeholder('Reason for change...')
                                                            ->columnSpan(1),
                                                    ]),
                                            ]),
                                        
                                        Repeater::make('after_snapshot.manpower_details')
                                            ->label('Revised Personnel Composition')
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('job_position_name')
                                                            ->label('Job Position / Rank')
                                                            ->required(),
                                                        TextInput::make('quantity')
                                                            ->label('Quantity')
                                                            ->numeric()
                                                            ->required(),
                                                    ]),
                                            ]),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
