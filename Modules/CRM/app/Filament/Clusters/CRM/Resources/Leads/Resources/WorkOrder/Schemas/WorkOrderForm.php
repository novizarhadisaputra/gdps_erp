<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\WorkOrder\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->description('Basic identification and source of the work order.')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        TextInput::make('number')
                            ->label('WO Number')
                            ->placeholder('Auto-generated')
                            ->disabled()
                            ->hiddenOn(['create'])
                            ->dehydrated(false),
                        Select::make('proposal_id')
                            ->label('Source Proposal')
                            ->relationship('proposal', 'number', fn ($query, $record) => $query->where('lead_id', $record?->lead_id))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),
                        DatePicker::make('order_date')
                            ->label('Order Date')
                            ->default(now())
                            ->required()
                            ->native(false),
                        TextInput::make('amount')
                            ->label('Total Amount')
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('IDR')
                            ->required(),
                    ])->columns(2),
                Section::make('Attachments')
                    ->description('Supporting documentation for this WO.')
                    ->icon('heroicon-m-paper-clip')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('attachment')
                            ->label('WO Document')
                            ->collection('wo_attachment')
                            ->disk('s3')
                            ->visibility('private')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
