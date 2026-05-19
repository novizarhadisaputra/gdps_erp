<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Schemas;

use App\Traits\ParsesCurrency;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\MasterData\Enums\Gender;

class ProposalForm
{
    use ParsesCurrency;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Grid::make(columns: 1)->schema([
                Section::make(__('Proposal Context'))
                    ->description(__('Core commercial information for this proposal.'))
                    ->schema([
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->label(__('Client / Customer'))
                            ->placeholder(__('Select customer'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->hidden()
                            ->default(fn ($livewire) => $livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->customer_id : null),

                        TextInput::make('title')
                            ->label(__('Proposal Title'))
                            ->placeholder(__('Enter proposal title'))
                            ->required()
                            ->disabled(fn ($record) => $record?->status !== ProposalStatus::Draft)
                            ->columnSpanFull()
                            ->helperText(__('The standardized name for this proposal.')),

                        Select::make('work_scheme_id')
                            ->relationship('workScheme', 'name')
                            ->label(__('Project Work Scheme'))
                            ->placeholder(__('Select work scheme'))
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated()
                            ->default(fn ($livewire) => $livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->work_scheme_id : null)
                            ->helperText(__('The operational model inherited from the Lead.')),

                        TextInput::make('amount_display')
                            ->label(__('Proposed Amount (Monthly)'))
                            ->prefix('IDR')
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->afterStateHydrated(function (TextInput $component, ?Model $record) {
                                if (! $record) {
                                    $component->state(0);

                                    return;
                                }

                                $pa = $record->profitabilityAnalysis ?? $record->lead?->profitabilityAnalyses()
                                    ->whereIn('status', [
                                        ProfitabilityAnalysisStatus::Approved,
                                        ProfitabilityAnalysisStatus::Submitted,
                                    ])->latest()->first();

                                $component->state($pa?->revenue_per_month ?? $record->amount ?? 0);
                            })
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->helperText(__('Automatically pulled from the latest approved Profitability Analysis.')),

                        TextInput::make('amount')
                            ->hidden()
                            ->dehydrated(),

                        Select::make('lead_id')
                            ->relationship('lead', 'title')
                            ->hidden()
                            ->dehydrated(),
                    ]),

                Section::make(__('Recipient Details'))
                    ->description(__('Select or manually enter the proposal recipient.'))
                    ->schema([
                        Select::make('recipient_contact_index')
                            ->label(__('Customer Contact Reference'))
                            ->options(function (Get $get, ?Proposal $record) {
                                $customerId = $get('customer_id') ?? $record?->customer_id;
                                $customer = $customerId ? Customer::find($customerId) : null;

                                return collect($customer?->contacts ?? [])
                                    ->mapWithKeys(function ($contact, $index) {
                                        $label = $contact['name'] ?? 'Unknown';
                                        $pos = $contact['position'] ?? $contact['job_position'] ?? $contact['role'] ?? $contact['type'] ?? 'No Position';

                                        return [$index => "{$label} ({$pos})"];
                                    });
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get, ?Proposal $record) {
                                if ($state === null || $state === '') {
                                    return;
                                }

                                $customerId = $get('customer_id') ?? $record?->customer_id;
                                $customer = $customerId ? Customer::find($customerId) : null;
                                $contacts = $customer?->contacts ?? [];
                                $contact = $contacts[$state] ?? null;

                                if ($contact) {
                                    $set('content_config.recipient_name', $contact['name'] ?? '');
                                    // Robust position check with role/type fallback
                                    $position = $contact['position'] ??
                                               $contact['job_position'] ??
                                               $contact['job_title'] ??
                                               $contact['title'] ??
                                               $contact['role'] ??
                                               $contact['type'] ??
                                               '';
                                    $set('content_config.recipient_title', $position);
                                    $set('content_config.recipient_gender', $contact['gender'] ?? Gender::Male->value);
                                }
                            })
                            ->createOptionForm([
                                Grid::make(3)->schema([
                                    Select::make('gender')
                                        ->options(Gender::class)
                                        ->required()
                                        ->native(false),
                                    TextInput::make('name')->required(),
                                    TextInput::make('job_position')->label(__('Job Position')),
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
                            ->placeholder(__('Pick a contact to auto-fill...'))
                            ->dehydrated(false)
                            ->helperText(__('Selecting a contact will populate the fields below.')),

                        Grid::make(3)->schema([
                            Select::make('content_config.recipient_gender')
                                ->label(__('Gender (Salutation)'))
                                ->options(Gender::class)
                                ->required()
                                ->native(false)
                                ->placeholder(__('Select salutation'))
                                ->helperText(__('Used for appropriate addressing in the formal letter.')),

                            TextInput::make('content_config.recipient_name')
                                ->label(__('Recipient Name'))
                                ->placeholder(__('Enter full name of the recipient'))
                                ->dehydrated()
                                ->required()
                                ->helperText(__('The person to whom this proposal is addressed.')),

                            TextInput::make('content_config.recipient_title')
                                ->label(__('Recipient Title/Position'))
                                ->dehydrated()
                                ->placeholder(__('e.g. Director of Finance, General Manager'))
                                ->helperText(__('Official job title of the recipient.')),
                        ]),
                    ]),

                Section::make(__('Proposal Content Customization'))
                    ->description(__('Tailor the wording and terms for this specific proposal.'))
                    ->collapsible()
                    ->schema([
                        RichEditor::make('content_config.intro_text')
                            ->label(__('Introductory Text'))
                            ->placeholder(__('Override default intro paragraph...'))
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull(),

                        Section::make(__('Service Packages Details (Bullet Points)'))
                            ->description(__('Customize the lists of what is included, complimentary, or excluded.'))
                            ->collapsed()
                            ->schema([
                                Repeater::make('content_config.included_items')
                                    ->label(__('A. What is Included (Cakupan Layanan)'))
                                    ->schema([
                                        RichEditor::make('item')
                                            ->toolbarButtons(['bold', 'italic', 'undo', 'redo'])
                                            ->columnSpanFull(),
                                    ])
                                    ->defaultItems(0)
                                    ->addActionLabel('Add point into List A')
                                    ->helperText(__('If empty, the standard list will be shown.')),

                                Repeater::make('content_config.complimentary_items')
                                    ->label(__('B. Complimentary (Layanan Tambahan Gratis)'))
                                    ->schema([
                                        RichEditor::make('item')
                                            ->toolbarButtons(['bold', 'italic', 'undo', 'redo'])
                                            ->columnSpanFull(),
                                    ])
                                    ->defaultItems(0)
                                    ->addActionLabel('Add point into List B')
                                    ->helperText(__('If empty, the standard list will be shown.')),

                                Repeater::make('content_config.excluded_items')
                                    ->label(__('C. What is Excluded (Hal yang Tidak Termasuk)'))
                                    ->schema([
                                        RichEditor::make('item')
                                            ->toolbarButtons(['bold', 'italic', 'undo', 'redo'])
                                            ->columnSpanFull(),
                                    ])
                                    ->defaultItems(0)
                                    ->addActionLabel('Add point into List C')
                                    ->helperText(__('If empty, the standard list will be shown.')),
                            ]),

                        RichEditor::make('content_config.closing_text')
                            ->label(__('Closing Text (Terms & Conditions Paragraph)'))
                            ->placeholder(__('Default: Formal GDPS closing statement...'))
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            TextInput::make('content_config.validity_period')
                                ->label(__('Validity Period (Days)'))
                                ->numeric()
                                ->default(30)
                                ->suffix('Days')
                                ->placeholder(__('e.g. 30'))
                                ->helperText(__('Number of days the proposal remains valid for acceptance.')),

                            TextInput::make('content_config.contact_phone')
                                ->label(__('Contact Phone Number'))
                                ->placeholder(__('e.g. 021-xxxxxx or +62 812...'))
                                ->helperText(__('The primary contact phone number displayed in the document header/footer.')),

                            TextInput::make('content_config.payment_term')
                                ->label(__('Payment Term Override (Days)'))
                                ->numeric()
                                ->suffix('Days')
                                ->placeholder(__('e.g. 60'))
                                ->helperText(__('If empty, defaults to the payment term specified in the PA/Lead.')),

                            Toggle::make('content_config.show_manpower_attachment')
                                ->label(__('Show Manpower Detail Attachment'))
                                ->default(true),
                            Toggle::make('content_config.show_material_attachment')
                                ->label(__('Show Tools & Equipment Detail Attachment'))
                                ->default(true),
                        ]),

                        Section::make(__('Miscellaneous (Lain-lain)'))
                            ->description(__('Additional terms or notes for the proposal.'))
                            ->collapsed()
                            ->schema([
                                Repeater::make('content_config.miscellaneous_items')
                                    ->label(__('Lain-lain Points'))
                                    ->schema([
                                        TextInput::make('item')
                                            ->placeholder(__('e.g. PT Customer menyediakan tempat penyimpanan...'))
                                            ->required()
                                            ->columnSpanFull(),
                                    ])
                                    ->defaultItems(0)
                                    ->addActionLabel('Add Miscellaneous Point')
                                    ->helperText(__('If empty, the standard items (a, b, c) from the template will be shown.')),
                            ]),

                    ]),

                Group::make()
                    ->schema([
                        Section::make(__('Identification'))
                            ->schema([
                                TextInput::make('number')
                                    ->label(__('Proposal #'))
                                    ->prefixIcon('heroicon-o-hashtag')
                                    ->placeholder(__('Auto-generated'))
                                    ->required()
                                    ->hiddenOn('create')
                                    ->disabled()
                                    ->dehydrated()
                                    ->copyable()
                                    ->unique(Proposal::class, 'number', ignoreRecord: true),

                                DatePicker::make('submission_date')
                                    ->label(__('Submission Date'))
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->native(false)
                                    ->disabled(fn ($record) => $record?->status !== ProposalStatus::Draft)
                                    ->placeholder(__('Select date'))
                                    ->helperText(__('When this proposal was sent to the client.')),

                                DatePicker::make('meeting_date')
                                    ->label(__('Meeting Date'))
                                    ->prefixIcon('heroicon-o-users')
                                    ->native(false)
                                    ->placeholder(__('Select date'))
                                    ->helperText(__('The date when the proposal meeting took place.')),
                            ]),

                        Section::make(__('Status'))
                            ->schema([
                                TextEntry::make('status')
                                    ->label(__('Proposal Status'))
                                    ->badge(),

                                TextEntry::make('is_imported')
                                    ->label(__('Source Reference'))
                                    ->state(fn ($record) => $record?->is_imported ? 'Imported from External System' : 'Created in ERP')
                                    ->visible(fn ($record) => $record?->is_imported)
                                    ->color('gray'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])->columnSpanFull(),

            Section::make(__('Final Documentation'))
                ->description(__('Upload the finalized, signed, or ready-to-send proposal document.'))
                ->schema([
                    SpatieMediaLibraryFileUpload::make('final_proposal')
                        ->collection('final_proposal')
                        ->label(__('Final Draft Proposal'))
                        ->disk('s3')
                        ->visibility('private')
                        ->downloadable()
                        ->openable()
                        ->helperText(__('Format: PDF preferred. Size limit 10MB.')),
                    SpatieMediaLibraryFileUpload::make('signed_proposal')
                        ->collection('signed_proposal')
                        ->label(__('Signed Proposal (Client Copy)'))
                        ->disk('s3')
                        ->visibility('private')
                        ->downloadable()
                        ->openable()
                        ->helperText(__('The officially signed and stamped proposal.')),
                ])
                ->columnSpanFull(),
        ];
    }
}
