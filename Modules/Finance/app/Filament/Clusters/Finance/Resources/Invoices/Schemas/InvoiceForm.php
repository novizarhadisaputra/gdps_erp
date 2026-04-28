<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Customer;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\MasterData\Enums\Gender;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice Identification')
                    ->description('Define the core identity, reference numbers, and issuance dates for this invoice.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('number')
                            ->label('Invoice Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g. INV/2024/001')
                            ->helperText('The unique identifier for this invoice. Leave blank for automatic sequencing.'),
                        DatePicker::make('invoice_date')
                            ->label('Invoice Date')
                            ->required()
                            ->default(now())
                            ->placeholder('Select date')
                            ->helperText('The official issuance date of this invoice.'),
                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required()
                            ->default(now()->addDays(30))
                            ->placeholder('Select due date')
                            ->helperText('The deadline for payment completion.'),
                        TextInput::make('invoice_type')
                            ->label('Invoice Label')
                            ->placeholder('e.g. Invoice, Debit Note, Proforma')
                            ->default('Invoice')
                            ->helperText('Customizes the document title on the generated PDF.'),
                        Hidden::make('status')
                            ->default(InvoiceStatus::Draft),
                    ])->columns(2),

                Section::make('Related Documents')
                    ->description('Link this invoice to its source documents and customer account.')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->placeholder('Search and select a customer...')
                            ->helperText('The client account that will be billed for this invoice.'),
                        MorphToSelect::make('sourceable')
                            ->label('Reference Document')
                            ->types([
                                MorphToSelect\Type::make(\Modules\CRM\Models\SalesOrder::class)
                                    ->titleAttribute('number')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->number} - {$record->customer?->name}"),
                                MorphToSelect\Type::make(\Modules\Project\Models\WorkCompletionReport::class)
                                    ->titleAttribute('number')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->number} - {$record->customer?->name}"),
                                MorphToSelect\Type::make(\Modules\CRM\Models\PurchaseOrder::class)
                                    ->titleAttribute('number')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->number} - {$record->customer?->name}"),
                                MorphToSelect\Type::make(\Modules\CRM\Models\WorkOrder::class)
                                    ->titleAttribute('number')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->number} - {$record->customer?->name}"),
                                MorphToSelect\Type::make(\Modules\CRM\Models\CooperationAgreement::class)
                                    ->titleAttribute('number')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->number} - {$record->customer?->name}"),
                                MorphToSelect\Type::make(\Modules\CRM\Models\MinutesOfAgreement::class)
                                    ->titleAttribute('number')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->number} - {$record->customer?->name}"),
                            ])
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->placeholder('Select a source document')
                            ->helperText('The original document this invoice is based on.')
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if (! $state) {
                                    return;
                                }

                                if (is_array($state)) {
                                    $modelClass = $state['type'] ?? null;
                                    $id = $state['id'] ?? null;
                                } else {
                                    [$modelClass, $id] = explode(':', $state);
                                }

                                if (! $modelClass || ! $id) {
                                    return;
                                }

                                $source = $modelClass::find($id);

                                if (! $source) {
                                    return;
                                }

                                // Sync customer
                                if (isset($source->customer_id)) {
                                    $set('customer_id', $source->customer_id);
                                }

                                // Sync items and financial details
                                if ($source instanceof \Modules\Project\Models\WorkCompletionReport) {
                                    $set('tax_percentage', $source->tax_percentage ?? 12);
                                    $set('tax_wording', $source->tax_wording);
                                    $set('content_config.recipient_name', $source->content_config['recipient_name'] ?? null);
                                    $set('content_config.recipient_title', $source->content_config['recipient_title'] ?? null);
                                    $set('content_config.recipient_gender', $source->content_config['recipient_gender'] ?? null);

                                    if (! empty($source->items)) {
                                        $set('items', $source->items);
                                        $sum = collect($source->items)->sum('total_price');
                                        $set('amount', $sum);
                                        $taxPercent = $source->tax_percentage ?? '12';
                                        $tax = $sum * ($taxPercent / 100);
                                        $set('tax_amount', $tax);
                                        $set('total_amount', $sum + $tax);
                                    }

                                    // Handle Internal Bank Account via BAPP
                                    if ($source->sourceable instanceof \Modules\CRM\Models\SalesOrder && $source->sourceable->type->value === \Modules\CRM\Enums\SalesOrderType::Internal->value) {
                                        $internalBank = \Modules\MasterData\Models\BankAccount::where('account_name', 'like', '%Internal%')
                                            ->where('is_active', true)
                                            ->first();

                                        if ($internalBank) {
                                            $set('bank_account_id', $internalBank->id);
                                            $set('payment_info', [
                                                'account_name' => $internalBank->account_name,
                                                'banks' => [
                                                    [
                                                        'bank_name' => $internalBank->bank_name,
                                                        'account_number' => $internalBank->account_number,
                                                        'currency' => $internalBank->currency,
                                                    ],
                                                ],
                                            ]);
                                        }
                                    }
                                } elseif ($source instanceof \Modules\CRM\Models\SalesOrder) {
                                    $soItems = $source->content_config['items'] ?? [];
                                    if (! empty($soItems)) {
                                        $invoiceItems = collect($soItems)->map(fn ($item) => [
                                            'item_name' => $item['description'] ?? $item['item_name'] ?? 'Item',
                                            'quantity' => $item['quantity'] ?? 0,
                                            'uom' => $item['uom'] ?? 'Unit',
                                            'unit_price' => $item['unit_price'] ?? 0,
                                            'total_price' => $item['total_price'] ?? 0,
                                        ])->toArray();

                                        $set('items', $invoiceItems);
                                        $sum = collect($invoiceItems)->sum('total_price');
                                        $set('amount', $sum);
                                        $taxPercent = $get('tax_percentage') ?? '12';
                                        $tax = $sum * ($taxPercent / 100);
                                        $set('tax_amount', $tax);
                                        $set('total_amount', $sum + $tax);
                                    }
                                } else {
                                    // Default for other types (PO, SPK, PKS)
                                    if (isset($source->items)) {
                                        $set('items', $source->items);
                                        $sum = collect($source->items)->sum('total_price');
                                        $set('amount', $sum);
                                        $set('tax_amount', $sum * 0.12);
                                        $set('total_amount', $sum * 1.12);

                                        // Handle Internal Bank Account
                                        if ($source->type->value === \Modules\CRM\Enums\SalesOrderType::Internal->value) {
                                            $internalBank = \Modules\MasterData\Models\BankAccount::where('account_name', 'like', '%Internal%')
                                                ->where('is_active', true)
                                                ->first();

                                            if ($internalBank) {
                                                $set('bank_account_id', $internalBank->id);
                                                $set('payment_info', [
                                                    'account_name' => $internalBank->account_name,
                                                    'banks' => [
                                                        [
                                                            'bank_name' => $internalBank->bank_name,
                                                            'account_number' => $internalBank->account_number,
                                                            'currency' => $internalBank->currency,
                                                        ],
                                                    ],
                                                ]);
                                            }
                                        }
                                    }
                                }
                            })
                            ->columnSpanFull(),
                    ])->columns(1),

                Section::make('Customer Signatory')
                    ->description('Select or manually enter the person who will receive or sign this invoice on behalf of the customer.')
                    ->schema([
                        Select::make('recipient_contact_index')
                            ->label('Customer Contact Reference')
                            ->options(function (Get $get) {
                                $customerId = $get('customer_id');
                                if (! $customerId) {
                                    return [];
                                }
                                $customer = Customer::find($customerId);
                                if (! $customer || empty($customer->contacts)) {
                                    return [];
                                }

                                return collect($customer->contacts)
                                    ->mapWithKeys(function ($contact, $index) {
                                        $label = $contact['name'] ?? 'Unknown';
                                        $pos = $contact['position'] ?? $contact['job_position'] ?? $contact['role'] ?? $contact['type'] ?? 'No Position';

                                        return [$index => "{$label} ({$pos})"];
                                    })
                                    ->toArray();
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if ($state === null || $state === '') {
                                    return;
                                }
                                $customerId = $get('customer_id');
                                if (! $customerId) {
                                    return;
                                }
                                $customer = Customer::find($customerId);
                                if (! $customer || empty($customer->contacts)) {
                                    return;
                                }
                                $contact = $customer->contacts[$state] ?? null;
                                if ($contact) {
                                    $set('content_config.recipient_name', $contact['name'] ?? '');

                                    $position = $contact['position'] ??
                                               $contact['job_position'] ??
                                               $contact['job_title'] ??
                                               $contact['role'] ??
                                               $contact['type'] ??
                                               '';

                                    // If position is a UUID (from type field), we don't want to show it as a title
                                    if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $position)) {
                                        $position = '';
                                    }

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
                                    TextInput::make('name')
                                        ->required()
                                        ->placeholder('Full name'),
                                    TextInput::make('job_position')
                                        ->label('Job Position')
                                        ->placeholder('e.g. Manager'),
                                ]),
                                Grid::make(2)->schema([
                                    TextInput::make('email')
                                        ->email()
                                        ->placeholder('email@example.com'),
                                    TextInput::make('phone')
                                        ->tel()
                                        ->placeholder('+62...'),
                                ]),
                            ])
                            ->createOptionUsing(function (array $data, Get $get) {
                                $customerId = $get('customer_id');
                                if (! $customerId) {
                                    return null;
                                }
                                $customer = Customer::find($customerId);
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
                            ->placeholder('Select a contact to auto-fill...')
                            ->dehydrated(false)
                            ->helperText('Selecting a contact will automatically populate the details below.'),

                        Grid::make(3)->schema([
                            Select::make('content_config.recipient_gender')
                                ->label('Salutation')
                                ->options(Gender::class)
                                ->required()
                                ->placeholder('Select...')
                                ->native(false),

                            TextInput::make('content_config.recipient_name')
                                ->label('Recipient Name')
                                ->placeholder('Full name of the recipient')
                                ->required(),

                            TextInput::make('content_config.recipient_title')
                                ->label('Recipient Title/Position')
                                ->placeholder('e.g. Finance Director'),
                        ]),
                    ])
                    ->collapsible(),

                Section::make('Invoice Line Items')
                    ->description('Detailed breakdown of billable items for this invoice.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->label('Line Items')
                            ->addActionLabel('Add Invoice Item')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('item_name')
                                            ->label('Item Name / Description')
                                            ->required()
                                            ->columnSpanFull(),
                                        TextInput::make('quantity')
                                            ->label('Quantity')
                                            ->numeric()
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($get, $set) => $set('total_price', round(floatval($get('quantity') ?? 0) * floatval(str_replace('.', '', $get('unit_price') ?? 0))))),
                                        TextInput::make('uom')
                                            ->label('Unit')
                                            ->required(),
                                        TextInput::make('unit_price')
                                            ->label('Price / Unit')
                                            ->numeric()
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($get, $set) => $set('total_price', round(floatval($get('quantity') ?? 0) * floatval(str_replace('.', '', $get('unit_price') ?? 0))))),
                                        TextInput::make('total_price')
                                            ->label('Total Price')
                                            ->numeric()
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR')
                                            ->readonly(),
                                    ]),
                            ])
                            ->columnSpanFull()
                            ->reorderable()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Auto-calculate amount when items change
                                $sum = 0;
                                foreach ((array) $state as $item) {
                                    $sum += floatval($item['total_price'] ?? 0);
                                }
                                $set('amount', $sum);
                                $taxPercent = (float) $get('tax_percentage') ?? 12;
                                $tax = round($sum * ($taxPercent / 100));
                                $set('tax_amount', $tax);
                                $set('total_amount', $sum + $tax);
                            })
                            ->translatable(),
                    ])->collapsible(),

                Section::make('Financial Details')
                    ->description('Calculate the total billed amounts including taxes.')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Base Amount')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->placeholder('0')
                                    ->helperText('The principal billable amount excluding tax. Auto-calculated from items.')
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $amount = (float) str_replace('.', '', $state);
                                        $taxPercent = (float) $get('tax_percentage') ?? 12;
                                        $tax = round($amount * ($taxPercent / 100));
                                        $set('tax_amount', $tax);
                                        $set('total_amount', $amount + $tax);
                                    }),
                                Select::make('tax_percentage')
                                    ->label('VAT Percentage')
                                    ->options([
                                        '12' => 'PPN 12%',
                                        '11' => 'PPN 11%',
                                    ])
                                    ->default('12')
                                    ->selectablePlaceholder(false)
                                    ->native(false)
                                    ->live(onBlur: true)
                                    ->placeholder('Select tax')
                                    ->helperText('The applicable Value Added Tax (PPN) rate.')
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $amountString = $get('amount') ?? '0';
                                        $amount = (float) str_replace('.', '', $amountString);
                                        $taxPercent = (float) $state;
                                        $tax = round($amount * ($taxPercent / 100));
                                        $set('tax_amount', $tax);
                                        $set('total_amount', $amount + $tax);

                                        $set('tax_wording', [
                                            'id' => "PPN {$taxPercent}%",
                                            'en' => "{$taxPercent}% VAT",
                                        ]);
                                    }),
                                TextInput::make('tax_amount')
                                    ->label('Tax Amount')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->placeholder('0')
                                    ->helperText('Value Added Tax (PPN).')
                                    ->afterStateUpdated(function ($get, $set) {
                                        $amount = (float) str_replace('.', '', $get('amount') ?? 0);
                                        $tax = (float) str_replace('.', '', $get('tax_amount') ?? 0);
                                        $set('total_amount', $amount + $tax);
                                    }),
                                TextInput::make('total_amount')
                                    ->label('Total Amount')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->readonly()
                                    ->placeholder('0')
                                    ->helperText('The grand total to be paid by the customer.')
                                    ->required(),
                                TextInput::make('tax_wording')
                                    ->label('Tax Wording (PDF)')
                                    ->placeholder('e.g. PPN 12%')
                                    ->columnSpan(2)
                                    ->helperText('This text will appear in the tax row of the PDF summary.')
                                    ->translatable(),
                            ]),
                    ]),

                Section::make('Payment Information')
                    ->description('Select the bank account for remittance.')
                    ->schema([
                        Select::make('bank_account_id')
                            ->label('Bank Account')
                            ->options(\Modules\MasterData\Models\BankAccount::query()->where('is_active', true)->pluck('bank_name', 'id'))
                            ->searchable()
                            ->live()
                            ->required()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! $state) {
                                    return;
                                }
                                $bank = \Modules\MasterData\Models\BankAccount::find($state);
                                if ($bank) {
                                    $set('payment_info', [
                                        'account_name' => $bank->account_name,
                                        'banks' => [
                                            [
                                                'bank_name' => $bank->bank_name,
                                                'account_number' => $bank->account_number,
                                                'currency' => $bank->currency,
                                            ],
                                        ],
                                    ]);
                                }
                            }),
                        TextInput::make('payment_info.account_name')
                            ->label('Account Name (a.n.)')
                            ->required()
                            ->readonly(),
                        Repeater::make('payment_info.banks')
                            ->label('Bank Details (Snapshot)')
                            ->schema([
                                TextInput::make('bank_name')->readonly(),
                                TextInput::make('account_number')->readonly(),
                                TextInput::make('currency')->readonly(),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(3),
                    ]),

                Section::make('Attachments')
                    ->description('Upload physical or digital documents related to this invoice.')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('signed_invoice')
                            ->collection('signed_invoice')
                            ->label('Signed Invoice (Final Scan)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->helperText('Upload the scanned document that has been signed by the customer.')
                            ->visible(fn ($record) => $record && ! in_array($record->status, [InvoiceStatus::Draft, InvoiceStatus::Submitted])),
                        SpatieMediaLibraryFileUpload::make('payment_proof')
                            ->collection('payment_proof')
                            ->label('Payment Proof (Bukti Bayar)')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(5120)
                            ->helperText('Upload the official transfer receipt from the client (PDF/JPG/PNG, max 5MB).'),
                    ])->columns(2),

                Section::make('Approval History')
                    ->description('Digital signatures recorded during the approval process.')
                    ->schema([
                        Repeater::make('signatures')
                            ->relationship('signatures')
                            ->label('')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('user.name')
                                        ->label('Approver Name')
                                        ->disabled(),
                                    TextInput::make('role')
                                        ->label('Title/Role')
                                        ->disabled(),
                                    DateTimePicker::make('signed_at')
                                        ->label('Signed At')
                                        ->disabled(),
                                ]),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record?->signatures()->exists()),
            ]);
    }
}
