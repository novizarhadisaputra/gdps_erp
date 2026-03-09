<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Proposal;

class ProposalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Grid::make(columns: 2)->schema([
                Section::make('Proposal Context')
                    ->description('Core commercial information for this proposal.')
                    ->schema([
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->label('Client / Customer')
                            ->placeholder('Select customer')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->hidden()
                            ->default(fn ($livewire) => $livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->customer_id : null),

                        Select::make('work_scheme_id')
                            ->relationship('workScheme', 'name')
                            ->label('Project Work Scheme')
                            ->placeholder('Select work scheme')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->default(fn ($livewire) => $livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->work_scheme_id : null)
                            ->helperText('The operational model inherited from the Lead.'),

                        TextInput::make('amount')
                            ->label('Proposed Amount')
                            ->placeholder('0')
                            ->prefixIcon('heroicon-o-banknotes')
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('IDR')
                            ->default(fn ($livewire) => $livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->estimated_amount : 0)
                            ->dehydrateStateUsing(fn ($state) => self::parseCurrency($state))
                            ->required()
                            ->helperText('Total value including all service costs.'),

                        Select::make('lead_id')
                            ->relationship('lead', 'title')
                            ->hidden()
                            ->dehydrated(),
                    ]),

                \Filament\Schemas\Components\Group::make()
                    ->schema([
                        Section::make('Identification')
                            ->schema([
                                TextInput::make('proposal_number')
                                    ->label('Proposal #')
                                    ->prefixIcon('heroicon-o-hashtag')
                                    ->placeholder('Auto-generated')
                                    ->required()
                                    ->hiddenOn('create')
                                    ->disabled()
                                    ->dehydrated()
                                    ->unique(Proposal::class, 'proposal_number', ignoreRecord: true),

                                DatePicker::make('submission_date')
                                    ->label('Submission Date')
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->native(false)
                                    ->placeholder('Select date')
                                    ->helperText('When this proposal was sent to the client.'),
                            ]),

                        Section::make('Status')
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Proposal Status')
                                    ->badge(),

                                TextEntry::make('is_imported')
                                    ->label('Source Reference')
                                    ->state(fn ($record) => $record?->is_imported ? 'Imported from External System' : 'Created in ERP')
                                    ->visible(fn ($record) => $record?->is_imported)
                                    ->color('gray'),
                            ]),
                    ])
                    ->columnSpan(1),
            ])->columnSpanFull(),

            Section::make('Final Documentation')
                ->description('Upload the finalized, signed, or ready-to-send proposal document.')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('final_proposal')
                        ->collection('final_proposal')
                        ->label('Final Proposal Document')
                        ->disk('s3')
                        ->visibility('private')
                        ->downloadable()
                        ->openable()
                        ->helperText('Format: PDF preferred. Size limit 10MB.')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ];
    }

    protected static function parseCurrency($value): float
    {
        if (! $value) {
            return 0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = str_replace('.', '', $value);
        $clean = str_replace(',', '.', $clean);

        return (float) $clean;
    }
}
