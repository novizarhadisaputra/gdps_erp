<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
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

                Section::make('Recipient Details')
                    ->description('Select or manually enter the proposal recipient.')
                    ->schema([
                        Select::make('recipient_contact_index')
                            ->label('Customer Contact Reference')
                            ->options(fn (Proposal $record) => collect($record->customer?->contacts ?? [])
                                ->mapWithKeys(fn ($contact, $index) => [$index => $contact['name'].' ('.($contact['position'] ?? $contact['job_position'] ?? 'No Position').')'])
                            )
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Proposal $record) {
                                if ($state === null || $state === '') {
                                    return;
                                }
                                $contacts = $record->customer?->contacts ?? [];
                                $contact = $contacts[$state] ?? null;
                                if ($contact) {
                                    $set('content_config.recipient_name', $contact['name'] ?? '');
                                    $set('content_config.recipient_title', $contact['position'] ?? $contact['job_position'] ?? '');
                                    $set('content_config.recipient_gender', $contact['gender'] ?? \Modules\MasterData\Enums\Gender::Male->value);
                                }
                            })
                            ->createOptionForm([
                                Grid::make(3)->schema([
                                    Select::make('gender')
                                        ->options(\Modules\MasterData\Enums\Gender::class)
                                        ->required()
                                        ->native(false),
                                    TextInput::make('name')->required(),
                                    TextInput::make('job_position')->label('Job Position'),
                                ]),
                                Grid::make(2)->schema([
                                    TextInput::make('email')->email(),
                                    TextInput::make('phone')->tel(),
                                ]),
                            ])
                            ->createOptionUsing(function (array $data, Proposal $record) {
                                $customer = $record->customer;
                                if (! $customer) {
                                    return null;
                                }

                                $contacts = $customer->contacts ?? [];
                                $contacts[] = [
                                    'gender' => $data['gender'],
                                    'name' => $data['name'],
                                    'job_position' => $data['job_position'],
                                    'email' => $data['email'] ?? null,
                                    'phone' => $data['phone'] ?? null,
                                    'type' => null,
                                ];

                                $customer->contacts = $contacts;
                                $customer->save();

                                return count($contacts) - 1;
                            })
                            ->placeholder('Pick a contact to auto-fill...')
                            ->helperText('Selecting a contact will populate the fields below.'),

                        Grid::make(3)->schema([
                            Select::make('content_config.recipient_gender')
                                ->label('Gender (Salutation)')
                                ->options(\Modules\MasterData\Enums\Gender::class)
                                ->required()
                                ->native(false),

                            TextInput::make('content_config.recipient_name')
                                ->label('Recipient Name')
                                ->placeholder('Enter full name')
                                ->required(),

                            TextInput::make('content_config.recipient_title')
                                ->label('Recipient Title/Position')
                                ->placeholder('e.g. Director of Finance'),
                        ]),
                    ]),

                Section::make('Proposal Content Customization')
                    ->description('Tailor the wording and terms for this specific proposal.')
                    ->collapsible()
                    ->schema([
                        Textarea::make('content_config.intro_text')
                            ->label('Introductory Text')
                            ->placeholder('Override default intro paragraph...')
                            ->rows(3),

                        Section::make('Service Packages Details (Bullet Points)')
                            ->description('Customize the lists of what is included, complimentary, or excluded.')
                            ->collapsed()
                            ->schema([
                                Repeater::make('content_config.included_items')
                                    ->label('A. What is Included (Cakupan Layanan)')
                                    ->simple(TextInput::make('item'))
                                    ->defaultItems(0)
                                    ->addActionLabel('Add point into List A')
                                    ->helperText('If empty, the standard list will be shown.'),

                                Repeater::make('content_config.complimentary_items')
                                    ->label('B. Complimentary (Layanan Tambahan Gratis)')
                                    ->simple(TextInput::make('item'))
                                    ->defaultItems(0)
                                    ->addActionLabel('Add point into List B')
                                    ->helperText('If empty, the standard list will be shown.'),

                                Repeater::make('content_config.excluded_items')
                                    ->label('C. What is Excluded (Hal yang Tidak Termasuk)')
                                    ->simple(TextInput::make('item'))
                                    ->defaultItems(0)
                                    ->addActionLabel('Add point into List C')
                                    ->helperText('If empty, the standard list will be shown.'),
                            ]),

                        Textarea::make('content_config.closing_text')
                            ->label('Closing Text (Terms & Conditions Paragraph)')
                            ->rows(3)
                            ->placeholder('Default: Formal GDPS closing statement...'),

                        Grid::make(2)->schema([
                            TextInput::make('content_config.validity_period')
                                ->label('Validity Period (Days)')
                                ->numeric()
                                ->default(30)
                                ->suffix('Days'),
                            TextInput::make('content_config.payment_term')
                                ->label('Payment Term (TOP)')
                                ->placeholder('Example: 60')
                                ->helperText('Leave empty to use PA default.')
                                ->suffix('Days'),
                        ]),

                        Grid::make(2)->schema([
                            Toggle::make('content_config.show_manpower_attachment')
                                ->label('Show Manpower Detail Attachment')
                                ->default(true),
                            Toggle::make('content_config.show_material_attachment')
                                ->label('Show Material Detail Attachment')
                                ->default(true),
                        ])->columns(2),
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
