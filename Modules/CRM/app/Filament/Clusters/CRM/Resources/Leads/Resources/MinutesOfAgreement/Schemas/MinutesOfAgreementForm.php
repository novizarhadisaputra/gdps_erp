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
                Section::make(__('General Information'))
                    ->description(__('Basic identification and source of the agreement.'))
                    ->icon('heroicon-m-information-circle')
                    ->schema([

                        TextInput::make('number')
                            ->label(__('MOA Number'))
                            ->placeholder(__('Auto-generated'))
                            ->disabled()
                            ->helperText(__('The unique reference number for this MOA.'))
                            ->hiddenOn(operations: ['create'])
                            ->dehydrated(false),
                        Select::make('proposal_id')
                            ->label(__('Source Proposal'))
                            ->relationship('proposal', 'number', fn ($query, $record) => $query->where('lead_id', $record?->lead_id))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->helperText(__('Select the proposal that this agreement is based on.')),
                        DatePicker::make('negotiation_date')
                            ->label(__('Negotiation Date'))
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->helperText(__('The date when the terms were agreed upon.')),

                        TextInput::make('amount')
                            ->label(__('Agreed Amount'))
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('IDR')
                            ->required()
                            ->helperText(__('The total value agreed in this MOA.')),
                        TextEntry::make('status')
                            ->label(__('Current Status'))
                            ->badge()
                            ->visibleOn('view'),

                    ])->columns(columns: 2),
                Section::make(__('Agreement Details'))
                    ->description(__('Detailed terms, scope, and supporting documentation.'))
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('attachment')
                            ->label(__('MoA Document'))
                            ->collection('moa_attachment')
                            ->disk('s3')
                            ->visibility('private')
                            ->required()
                            ->columnSpanFull()
                            ->helperText(__('Upload the signed or final agreement document.')),
                        Grid::make(2)
                            ->schema([
                                Textarea::make('scope_of_work')
                                    ->label(__('Scope of Work'))
                                    ->rows(3)
                                    ->placeholder(__('Define the project boundaries and deliverables...'))
                                    ->columnSpanFull()
                                    ->translatable(),
                                Textarea::make('timeline')
                                    ->label(__('Timeline'))
                                    ->rows(3)
                                    ->placeholder(__('Specify key dates or duration...'))
                                    ->columnSpan(1)
                                    ->translatable(),
                                Textarea::make('terms')
                                    ->label(__('Terms & Conditions'))
                                    ->rows(3)
                                    ->placeholder(__('Specific payment terms, duties, etc...'))
                                    ->columnSpan(1)
                                    ->translatable(),
                                Textarea::make('notes')
                                    ->label(__('Additional Notes'))
                                    ->rows(3)
                                    ->placeholder(__('Any other relevant details...'))
                                    ->columnSpanFull()
                                    ->translatable(),
                            ]),
                    ]),
            ]);
    }
}
