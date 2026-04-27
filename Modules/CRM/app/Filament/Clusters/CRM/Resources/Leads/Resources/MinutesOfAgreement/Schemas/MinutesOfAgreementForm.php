<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MinutesOfAgreementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->description('Basic identification and source of the agreement.')
                    ->icon('heroicon-m-information-circle')
                    ->schema([

                        TextInput::make('number')
                            ->label('MOA Number')
                            ->placeholder('Auto-generated')
                            ->disabled()
                            ->helperText('The unique reference number for this MOA.')
                            ->hiddenOn(operations: ['create'])
                            ->dehydrated(false),
                        Select::make('proposal_id')
                            ->label('Source Proposal')
                            ->relationship('proposal', 'number', fn ($query, $record) => $query->where('lead_id', $record?->lead_id))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->helperText('Select the proposal that this agreement is based on.'),
                        DatePicker::make('negotiation_date')
                            ->label('Negotiation Date')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->helperText('The date when the terms were agreed upon.'),

                        TextInput::make('amount')
                            ->label('Agreed Amount')
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('IDR')
                            ->required()
                            ->helperText('The total value agreed in this MOA.'),
                        TextEntry::make('status')
                            ->label('Current Status')
                            ->badge()
                            ->visibleOn('view'),

                    ])->columns(columns: 2),
                Section::make('Agreement Details')
                    ->description('Detailed terms, scope, and supporting documentation.')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('attachment')
                            ->label('MoA Document')
                            ->collection('moa_attachment')
                            ->disk('s3')
                            ->visibility('private')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Upload the signed or final agreement document.'),
                        Grid::make(2)
                            ->schema([
                                Textarea::make('scope_of_work')
                                    ->label('Scope of Work')
                                    ->rows(3)
                                    ->placeholder('Define the project boundaries and deliverables...')
                                    ->columnSpanFull()
                                    ->translatable(),
                                Textarea::make('timeline')
                                    ->label('Timeline')
                                    ->rows(3)
                                    ->placeholder('Specify key dates or duration...')
                                    ->columnSpan(1)
                                    ->translatable(),
                                Textarea::make('terms')
                                    ->label('Terms & Conditions')
                                    ->rows(3)
                                    ->placeholder('Specific payment terms, duties, etc...')
                                    ->columnSpan(1)
                                    ->translatable(),
                                Textarea::make('notes')
                                    ->label('Additional Notes')
                                    ->rows(3)
                                    ->placeholder('Any other relevant details...')
                                    ->columnSpanFull()
                                    ->translatable(),
                            ]),
                    ]),
            ]);
    }
}
