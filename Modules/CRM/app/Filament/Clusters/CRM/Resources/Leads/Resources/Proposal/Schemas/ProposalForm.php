<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\ProposalStatus;
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

                        TextInput::make('title')
                            ->label('Proposal Title')
                            ->placeholder('Enter proposal title')
                            ->required()
                            ->disabled(fn ($record) => $record?->status !== ProposalStatus::Draft)
                            ->columnSpanFull()
                            ->helperText('The standardized name for this proposal.'),

                        Select::make('work_scheme_id')
                            ->relationship('workScheme', 'name')
                            ->label('Project Work Scheme')
                            ->placeholder('Select work scheme')
                            ->searchable()
                            ->preload()
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
                            ->disabled(fn ($record) => $record?->status !== ProposalStatus::Draft)
                            ->default(fn ($livewire) => $livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->estimated_amount : 0)
                            ->dehydrateStateUsing(fn ($state) => self::parseCurrency($state))
                            ->helperText('Total value including all service costs.'),

                        Select::make('lead_id')
                            ->relationship('lead', 'title')
                            ->hidden()
                            ->dehydrated(),
                    ]),

                Group::make()
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
                                    ->disabled(fn ($record) => $record?->status !== ProposalStatus::Draft)
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
                        ->label('Final Draft Proposal')
                        ->disk('s3')
                        ->visibility('private')
                        ->downloadable()
                        ->openable()
                        ->hintAction(
                            fn (?Proposal $record) => $record ? Action::make('downloadPdf')
                                ->label('Download Draft PDF')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->action(function () use ($record) {
                                    $record->load([
                                        'customer',
                                        'profitabilityAnalysis.workScheme',
                                        'profitabilityAnalysis.paymentTerm',
                                        'profitabilityAnalysis.productCluster',
                                        'lead.user',
                                        'lead.ams',
                                        'lead.manpowerTemplates.items.jobPosition',
                                        'lead.costingTemplates.costingTemplateItems.item',
                                        'lead.latestGeneralInformation',
                                        'lead.salesPlan',
                                    ]);

                                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.proposal', ['record' => $record]);
                                    $filename = str_replace(['/', '\\'], '-', $record->proposal_number);

                                    return response()->streamDownload(fn () => print ($pdf->output()), "proposal-{$filename}.pdf");
                                }) : null
                        )
                        ->helperText('Format: PDF preferred. Size limit 10MB.'),
                    SpatieMediaLibraryFileUpload::make('signed_proposal')
                        ->collection('signed_proposal')
                        ->label('Signed Proposal (Client Copy)')
                        ->disk('s3')
                        ->visibility('private')
                        ->downloadable()
                        ->openable()
                        ->helperText('The officially signed and stamped proposal.'),
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
