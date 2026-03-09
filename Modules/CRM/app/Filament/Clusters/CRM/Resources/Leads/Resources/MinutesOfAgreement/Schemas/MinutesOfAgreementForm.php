<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\MoAStatus;

class MinutesOfAgreementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('moa_number')
                                    ->label('MOA Number')
                                    ->placeholder('Auto-generated')
                                    ->disabled()
                                    ->hiddenOn(operations: ['create'])
                                    ->dehydrated(false),
                                DatePicker::make('negotiation_date')
                                    ->label('Negotiation Date')
                                    ->default(now())
                                    ->required()
                                    ->native(false),
                                Select::make('status')
                                    ->options(MoAStatus::class)
                                    ->default(MoAStatus::Draft)
                                    ->required()
                                    ->native(false),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('proposal_id')
                                    ->label('Source Proposal')
                                    ->relationship('proposal', 'proposal_number', fn ($query, $record) => $query->where('lead_id', $record?->lead_id))
                                    ->searchable()
                                    ->preload()
                                    ->live(),
                                TextInput::make('amount')
                                    ->label('Agreed Amount')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR')
                                    ->required(),
                            ]),
                    ]),
                Section::make('Agreement Details')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('attachment')
                            ->label('MoA Document')
                            ->collection('moa_attachment')
                            ->disk('s3')
                            ->visibility('private')
                            ->required(),
                        Textarea::make('scope_of_work')
                            ->label('Scope of Work')
                            ->rows(3),
                        Textarea::make('timeline')
                            ->label('Timeline')
                            ->rows(3),
                        Textarea::make('terms')
                            ->label('Terms & Conditions')
                            ->rows(3),
                        Textarea::make('notes')
                            ->label('Additional Notes')
                            ->rows(3),
                    ]),
            ]);
    }
}
