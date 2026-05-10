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
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Modules\CRM\Enums\SalesOrderType;
use Modules\CRM\Models\CooperationAgreement;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\MinutesOfAgreement;
use Modules\CRM\Models\PurchaseOrder;
use Modules\CRM\Models\SalesOrder;
use Modules\CRM\Models\WorkOrder;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\MasterData\Enums\Gender;
use Modules\MasterData\Models\AppSetting;
use Modules\MasterData\Models\BankAccount;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\RevenueType;
use Modules\MasterData\Models\Tax;
use Modules\Project\Models\WorkCompletionReport;

class InvoiceForm
{
    private static function parseNumber($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (! is_string($value)) {
            return 0;
        }

        return (float) str_replace(',', '.', str_replace('.', '', $value));
    }

    public static function recalculateTotals(Get $get, Set $set): void
    {
        $amount = self::parseNumber($get('amount'));
        $vatAmount = self::parseNumber($get('tax_amount'));

        // Calculate Withholding Taxes from Repeater
        $taxDetails = $get('tax_details') ?? [];
        $withholdingTotal = 0;

        foreach ($taxDetails as $detail) {
            $withholdingTotal += self::parseNumber($detail['tax_amount'] ?? 0);
        }

        $set('withholding_tax_amount', $withholdingTotal);
        $set('total_amount', $amount + $vatAmount - $withholdingTotal);
    }

    public static function resolveBaseAmount(string $basis, Get $get): float
    {
        $amount = self::parseNumber($get('amount'));

        if ($basis === 'management_fee') {
            $items = $get('items') ?? [];
            $activeItems = is_array($items) && isset($items['id']) ? $items['id'] : $items;

            $additionalTypeIds = RevenueType::where('code', 'additional')->pluck('id')->toArray();

            return collect($activeItems)->filter(function ($item) use ($additionalTypeIds) {
                if (isset($item['revenue_type_id']) && in_array($item['revenue_type_id'], $additionalTypeIds)) {
                    return true;
                }
                $name = strtolower($item['item_name'] ?? '');

                return str_contains($name, 'management fee') || str_contains($name, 'fee');
            })->sum(function ($item) {
                return self::parseNumber($item['total_price'] ?? 0);
            });
        }

        return $amount;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('snapshot'),
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

                Grid::make(2)
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Related Documents')
                            ->description('Link this invoice to its source documents and customer account.')
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->options(Customer::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->placeholder('Search and select a customer...')
                                    ->helperText('The client account that will be billed for this invoice.'),
                                Select::make('project_area_id')
                                    ->label('Project Area')
                                    ->options(ProjectArea::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->placeholder('Select area')
                                    ->helperText('The geographical area or segment this invoice belongs to.'),
                                MorphToSelect::make('sourceable')
                                    ->label('Reference Document')
                                    ->types([
                                        MorphToSelect\Type::make(SalesOrder::class)
                                            ->label('Sales Order (SO)')
                                            ->titleAttribute('number')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->number} - {$record->customer?->name}"),
                                        MorphToSelect\Type::make(WorkCompletionReport::class)
                                            ->label('Work Completion Report (BAPP)')
                                            ->titleAttribute('number')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->number} - {$record->customer?->name}"),
                                        MorphToSelect\Type::make(PurchaseOrder::class)
                                            ->label('Purchase Order (PO)')
                                            ->titleAttribute('number')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->number} - {$record->customer?->name}"),
                                        MorphToSelect\Type::make(WorkOrder::class)
                                            ->label('Work Order (SPK)')
                                            ->titleAttribute('number')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->number} - {$record->customer?->name}"),
                                        MorphToSelect\Type::make(CooperationAgreement::class)
                                            ->label('Cooperation Agreement (PKS)')
                                            ->titleAttribute('number')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->number} - {$record->customer?->name}"),
                                        MorphToSelect\Type::make(MinutesOfAgreement::class)
                                            ->label('Minutes of Agreement (MOA)')
                                            ->titleAttribute('number')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->number} - {$record->customer?->name}"),
                                    ])
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required()
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

                                        // Set Snapshot from source
                                        $set('snapshot', $source->snapshot);

                                        // Sync items and financial details
                                        if ($source instanceof WorkCompletionReport) {
                                            $set('tax_id', $source->tax_id);
                                            $defaultVat = Tax::getDefaultRate('sales', AppSetting::getPayload('finance', 'global_financial_parameters')['vat_rate'] ?? 11.00);
                                            $set('tax_percentage', $source->tax_percentage ?? $defaultVat);
                                            $set('project_area_id', $source->project_area_id);
                                            $set('tax_wording', $source->tax_wording);
                                            $set('content_config.recipient_name', $source->content_config['recipient_name'] ?? null);
                                            $set('content_config.recipient_title', $source->content_config['recipient_title'] ?? null);
                                            $set('content_config.recipient_gender', $source->content_config['recipient_gender'] ?? null);

                                            if (! empty($source->getTranslations('items'))) {
                                                $set('items', $source->getTranslations('items'));
                                                $items = $source->items ?? []; // Current locale items for calculation
                                                $sum = collect($items)->sum('total_price');
                                                $set('amount', $sum);
                                                $taxId = $source->tax_id;
                                                $taxRecord = $taxId ? Tax::find($taxId) : null;
                                                $defaultVat = Tax::getDefaultRate('sales', AppSetting::getPayload('finance', 'global_financial_parameters')['vat_rate'] ?? 11.00);
                                                $tax = $taxRecord ? $taxRecord->calculateTax($sum) : round($sum * (($get('tax_percentage') ?? $defaultVat) / 100));
                                                $set('tax_amount', $tax);
                                                $set('total_amount', $sum + $tax);
                                            }

                                            // Handle Internal Bank Account via BAPP
                                            if ($source->sourceable instanceof SalesOrder && $source->sourceable->type->value === SalesOrderType::Internal->value) {
                                                $internalBank = BankAccount::where('account_name', 'like', '%Internal%')
                                                    ->where('is_active', true)
                                                    ->first();

                                                if ($internalBank) {
                                                    $set('bank_account_id', $internalBank->id);
                                                    $set('payment_info', [
                                                        'account_name' => $internalBank->account_name,
                                                        'banks' => [
                                                            Str::uuid()->toString() => [
                                                                'bank_name' => $internalBank->bank_name,
                                                                'account_number' => $internalBank->account_number,
                                                                'currency' => $internalBank->currency,
                                                            ],
                                                        ],
                                                    ]);
                                                }
                                            }
                                        } elseif ($source instanceof SalesOrder) {
                                            $set('project_area_id', $source->project_area_id);
                                            $set('tax_id', $source->tax_id);

                                            $soItems = $source->content_config['items'] ?? [];
                                            if (! empty($soItems)) {
                                                $invoiceItems = collect($soItems)->mapWithKeys(fn ($item) => [
                                                    Str::uuid()->toString() => [
                                                        'item_name' => $item['description'] ?? $item['item_name'] ?? 'Item',
                                                        'quantity' => $item['quantity'] ?? 0,
                                                        'uom' => $item['uom'] ?? 'Unit',
                                                        'unit_price' => $item['unit_price'] ?? 0,
                                                        'total_price' => $item['total_price'] ?? 0,
                                                        'revenue_type_id' => RevenueType::where('code', 'main')->first()?->id,
                                                    ],
                                                ])->toArray();

                                                // Set translations for both id and en
                                                $set('items', [
                                                    'id' => $invoiceItems,
                                                    'en' => $invoiceItems,
                                                ]);

                                                $sum = collect($invoiceItems)->sum('total_price');
                                                $set('amount', $sum);
                                                $taxId = $get('tax_id');
                                                $taxRecord = $taxId ? Tax::find($taxId) : null;
                                                $defaultVat = Tax::getDefaultRate('sales', AppSetting::getPayload('finance', 'global_financial_parameters')['vat_rate'] ?? 11.00);
                                                $tax = $taxRecord ? $taxRecord->calculateTax($sum) : round($sum * (($get('tax_percentage') ?? $defaultVat) / 100));
                                                $set('tax_amount', $tax);
                                                $set('total_amount', $sum + $tax);
                                            }
                                        } else {
                                            // Default for other types (PO, SPK, PKS)
                                            if (isset($source->items)) {
                                                $newItems = collect($source->items)->mapWithKeys(fn ($item) => [
                                                    Str::uuid()->toString() => is_array($item) ? $item : (array) $item,
                                                ])->toArray();
                                                $set('items', $newItems);
                                                $sum = collect($newItems)->sum('total_price');
                                                $set('amount', $sum);
                                                $defaultVat = Tax::getDefaultRate('sales', AppSetting::getPayload('finance', 'global_financial_parameters')['vat_rate'] ?? 11.00);
                                                $taxAmount = round($sum * ($defaultVat / 100));
                                                $set('tax_amount', $taxAmount);
                                                $set('total_amount', $sum + $taxAmount);

                                                // Handle Internal Bank Account
                                                if ($source->type->value === SalesOrderType::Internal->value) {
                                                    $internalBank = BankAccount::where('account_name', 'like', '%Internal%')
                                                        ->where('is_active', true)
                                                        ->first();

                                                    if ($internalBank) {
                                                        $set('bank_account_id', $internalBank->id);
                                                        $set('payment_info', [
                                                            'account_name' => $internalBank->account_name,
                                                            'banks' => [
                                                                Str::uuid()->toString() => [
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
                                        ->placeholder('Select gender')
                                        ->helperText('Gender-based salutation for the recipient (Bapak/Ibu).')
                                        ->native(false),

                                    TextInput::make('content_config.recipient_name')
                                        ->label('Recipient Name')
                                        ->placeholder('Full name of the recipient')
                                        ->helperText('The name of the individual who will receive the invoice.')
                                        ->required(),

                                    TextInput::make('content_config.recipient_title')
                                        ->label('Recipient Title/Position')
                                        ->placeholder('e.g. Finance Director')
                                        ->helperText('Official job title of the recipient.'),
                                ]),
                            ])
                            ->collapsible(),
                    ]),

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
                                            ->placeholder('e.g. Monthly Cleaning Service')
                                            ->helperText('Detailed description of the service or product.')
                                            ->columnSpan(2),
                                        Select::make('revenue_type_id')
                                            ->label('Revenue Category')
                                            ->options(RevenueType::where('is_active', true)->pluck('name', 'id'))
                                            ->required()
                                            ->default(fn () => RevenueType::where('is_default', true)->first()?->id)
                                            ->live()
                                            ->placeholder('Select category')
                                            ->helperText('Classify this item as Main or Additional Revenue.'),
                                        TextInput::make('quantity')
                                            ->label('Quantity')
                                            ->numeric()
                                            ->required()
                                            ->placeholder('1')
                                            ->helperText('Number of units being billed.')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                $qty = (float) ($get('quantity') ?? 0);
                                                $price = self::parseNumber($get('unit_price'));
                                                $set('total_price', round($qty * $price));
                                            }),
                                        TextInput::make('uom')
                                            ->label('Unit')
                                            ->required()
                                            ->placeholder('e.g. Month, Pcs, Service')
                                            ->helperText('The unit of measurement.'),
                                        TextInput::make('unit_price')
                                            ->label('Price / Unit')
                                            ->numeric()
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR')
                                            ->required()
                                            ->placeholder('0')
                                            ->helperText('Gross price per unit before tax.')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                $qty = (float) ($get('quantity') ?? 0);
                                                $price = self::parseNumber($get('unit_price'));
                                                $set('total_price', round($qty * $price));
                                            }),
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
                            ->translatable()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Auto-calculate amount when items change
                                $items = is_array($state) && isset($state['id']) ? $state['id'] : $state;

                                $sum = 0;
                                foreach ((array) $items as $item) {
                                    $sum += self::parseNumber($item['total_price'] ?? 0);
                                }
                                $set('amount', $sum);

                                // Update VAT Base Amount if basis is total
                                if ($get('tax_basis') === 'total' || ! $get('tax_basis')) {
                                    $set('tax_base_amount', $sum);

                                    $taxId = $get('tax_id');
                                    $taxRecord = $taxId ? Tax::find($taxId) : null;
                                    $defaultVat = Tax::getDefaultRate('sales', AppSetting::getPayload('finance', 'global_financial_parameters')['vat_rate'] ?? 11.00);
                                    $tax = $taxRecord ? $taxRecord->calculateTax($sum) : round($sum * (($get('tax_percentage') ?? $defaultVat) / 100));
                                    $set('tax_amount', $tax);
                                }

                                self::recalculateTotals($get, $set);
                            }),
                    ])->collapsible(),

                Section::make('Financial Details')
                    ->description('Calculate the total billed amounts including taxes.')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Base Amount (Gross)')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $sum = self::parseNumber($state);
                                        // Update VAT Base Amount if basis is total
                                        if ($get('tax_basis') === 'total' || ! $get('tax_basis')) {
                                            $set('tax_base_amount', $sum);

                                            $taxId = $get('tax_id');
                                            $taxRecord = $taxId ? Tax::find($taxId) : null;
                                            $defaultVat = Tax::getDefaultRate('sales', AppSetting::getPayload('finance', 'global_financial_parameters')['vat_rate'] ?? 11.00);
                                            $tax = $taxRecord ? $taxRecord->calculateTax($sum) : round($sum * (($get('tax_percentage') ?? $defaultVat) / 100));
                                            $set('tax_amount', $tax);
                                        }

                                        self::recalculateTotals($get, $set);
                                    }),
                            ]),

                        Grid::make(2)
                            ->columns(2)
                            ->columnSpanFull()
                            ->schema([
                                Section::make('Tax Information (VAT / PPN)')
                                    ->description('Configure the Value Added Tax calculation basis and rate.')
                                    ->columnSpan(1)
                                    ->schema([
                                        Select::make('tax_basis')
                                            ->label('Tax Basis (DPP)')
                                            ->options([
                                                'total' => 'Total Amount',
                                                'management_fee' => 'Management Fee Only',
                                                'custom' => 'Custom / Manual',
                                            ])
                                            ->default('total')
                                            ->live()
                                            ->required()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $baseAmount = self::resolveBaseAmount($state, $get);
                                                $set('tax_base_amount', $baseAmount);

                                                $taxRecord = Tax::find($get('tax_id'));
                                                $tax = $taxRecord ? $taxRecord->calculateTax($baseAmount) : round($baseAmount * (($get('tax_percentage') ?? 11) / 100));
                                                $set('tax_amount', $tax);

                                                self::recalculateTotals($get, $set);
                                            }),

                                        TextInput::make('tax_base_amount')
                                            ->label('Taxable Amount (DPP)')
                                            ->numeric()
                                            ->prefix('IDR')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->readOnly(fn (Get $get) => $get('tax_basis') !== 'custom')
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $baseAmount = self::parseNumber($state);
                                                $taxRecord = Tax::find($get('tax_id'));
                                                $tax = $taxRecord ? $taxRecord->calculateTax($baseAmount) : round($baseAmount * (($get('tax_percentage') ?? 11) / 100));

                                                $set('tax_amount', $tax);
                                                self::recalculateTotals($get, $set);
                                            }),

                                        Select::make('tax_id')
                                            ->label('VAT Category')
                                            ->options(Tax::where('category', 'sales')->pluck('name', 'id'))
                                            ->default(fn () => Tax::where('category', 'sales')->where('is_default', true)->first()?->id)
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                if (! $state) {
                                                    return;
                                                }

                                                $taxRecord = Tax::find($state);
                                                $taxPercent = $taxRecord?->rate ?? 0;
                                                $set('tax_percentage', $taxPercent);

                                                $baseAmount = self::parseNumber($get('tax_base_amount'));
                                                $taxAmount = $taxRecord ? $taxRecord->calculateTax($baseAmount) : round($baseAmount * ($taxPercent / 100));
                                                $set('tax_amount', $taxAmount);

                                                self::recalculateTotals($get, $set);

                                                $wording = "PPN {$taxPercent}%";
                                                $set('tax_wording', ['id' => $wording, 'en' => "{$taxPercent}% VAT"]);
                                            }),

                                        TextInput::make('tax_amount')
                                            ->label('Tax Amount (VAT)')
                                            ->numeric()
                                            ->prefix('IDR')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateTotals($get, $set)),
                                    ]),

                                Section::make('Withholding Taxes (PPh Potongan)')
                                    ->description('Add any income tax withholdings like PPh 23, PPh 4(2), etc.')
                                    ->columnSpan(1)
                                    ->schema([
                                        Repeater::make('tax_details')
                                            ->label('')
                                            ->addActionLabel('Add Potongan Pajak')
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        Select::make('tax_id')
                                                            ->label('Tax Type')
                                                            ->options(Tax::where('category', 'withholding')->pluck('name', 'id'))
                                                            ->required()
                                                            ->searchable()
                                                            ->preload()
                                                            ->live()
                                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                                if (! $state) {
                                                                    return;
                                                                }
                                                                $taxRecord = Tax::find($state);
                                                                $basis = $get('tax_basis') ?? 'total';
                                                                $baseAmount = self::resolveBaseAmount($basis, $get->get('../../../')); // Drill up to form root
                                                                $taxAmount = $taxRecord ? $taxRecord->calculateTax($baseAmount) : 0;

                                                                $set('tax_base_amount', $baseAmount);
                                                                $set('tax_amount', $taxAmount);
                                                            }),

                                                        Select::make('tax_basis')
                                                            ->label('Basis (DPP)')
                                                            ->options([
                                                                'total' => 'Total Amount',
                                                                'management_fee' => 'Management Fee',
                                                                'custom' => 'Custom',
                                                            ])
                                                            ->default('total')
                                                            ->live()
                                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                                $baseAmount = self::resolveBaseAmount($state, $get->get('../../../'));
                                                                $set('tax_base_amount', $baseAmount);

                                                                $taxRecord = Tax::find($get('tax_id'));
                                                                $taxAmount = $taxRecord ? $taxRecord->calculateTax($baseAmount) : 0;
                                                                $set('tax_amount', $taxAmount);
                                                            }),

                                                        TextInput::make('tax_base_amount')
                                                            ->label('Base Amount')
                                                            ->numeric()
                                                            ->prefix('IDR')
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                            ->readOnly(fn (Get $get) => $get('tax_basis') !== 'custom')
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                                $taxRecord = Tax::find($get('tax_id'));
                                                                $set('tax_amount', $taxRecord ? $taxRecord->calculateTax(self::parseNumber($state)) : 0);
                                                            }),

                                                        TextInput::make('tax_amount')
                                                            ->label('Amount')
                                                            ->numeric()
                                                            ->prefix('IDR')
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                            ->required()
                                                            ->live(onBlur: true),
                                                    ]),
                                            ])
                                            ->live()
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateTotals($get, $set))
                                            ->afterStateHydrated(fn (Get $get, Set $set) => self::recalculateTotals($get, $set)),
                                    ]),
                            ]),

                        Section::make('Financial Summary')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('amount')
                                            ->label('Base Amount (Gross)')
                                            ->numeric()
                                            ->prefix('IDR')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->readonly()
                                            ->helperText('Sum of all line items.'),

                                        TextInput::make('tax_amount')
                                            ->label('Total VAT (PPN)')
                                            ->numeric()
                                            ->prefix('IDR')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->readonly()
                                            ->helperText('Value Added Tax addition.'),

                                        TextInput::make('withholding_tax_amount')
                                            ->label('Total Withholding (PPh)')
                                            ->numeric()
                                            ->prefix('IDR')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->readonly()
                                            ->helperText('Sum of all tax deductions.'),

                                        TextInput::make('total_amount')
                                            ->label('Total Billed (Net)')
                                            ->numeric()
                                            ->prefix('IDR')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->readonly()
                                            ->required()
                                            ->helperText('Gross + VAT - Withholding.'),
                                    ]),

                                TextInput::make('tax_wording')
                                    ->label('Tax Wording (PDF)')
                                    ->placeholder('e.g. PPN 12%')
                                    ->columnSpanFull()
                                    ->helperText('This text will appear in the tax row of the PDF summary.')
                                    ->translatable(),
                            ]),
                    ]),

                Section::make('Payment Information')
                    ->description('Select the bank account for remittance.')
                    ->schema([
                        Select::make('bank_account_id')
                            ->label('Bank Account')
                            ->options(BankAccount::query()->where('is_active', true)->pluck('bank_name', 'id'))
                            ->createOptionForm([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('bank_name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('account_number')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('account_name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('currency')
                                            ->required()
                                            ->default('IDR')
                                            ->maxLength(3),
                                        TextInput::make('swift_code')
                                            ->maxLength(255),
                                        TextInput::make('account_code')
                                            ->maxLength(255),
                                        Toggle::make('is_active')
                                            ->default(true),
                                    ]),
                            ])
                            ->createOptionUsing(fn (array $data) => \Modules\MasterData\Models\BankAccount::create($data)->id)
                            ->searchable()
                            ->live()
                            ->required()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! $state) {
                                    return;
                                }
                                $bank = BankAccount::find($state);
                                if ($bank) {
                                    $set('payment_info', [
                                        'account_name' => $bank->account_name,
                                        'banks' => [
                                            \Illuminate\Support\Str::uuid()->toString() => [
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
                            ->readonly()
                            ->placeholder('Auto-filled from Bank')
                            ->helperText('The beneficiary name for this account.'),
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
                            ->visible(fn ($record) => $record && $record->isFullyApproved())
                            ->live()
                            ->afterStateUpdated(function ($state, $record) {
                                if ($state && $record && $record->status !== InvoiceStatus::Approved) {
                                    $record->update(['status' => InvoiceStatus::Approved]);
                                }
                            }),
                        SpatieMediaLibraryFileUpload::make('payment_proof')
                            ->collection('payment_proof')
                            ->label('Payment Proof (Bukti Bayar)')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(5120)
                            ->helperText('Upload the official transfer receipt from the client (PDF/JPG/PNG, max 5MB).')
                            ->visible(fn ($record) => $record && in_array($record->status, [InvoiceStatus::Sent, InvoiceStatus::Partial, InvoiceStatus::Overdue]))
                            ->live()
                            ->afterStateUpdated(function ($state, $record) {
                                if ($state && $record && $record->status !== InvoiceStatus::Paid) {
                                    $record->update(['status' => InvoiceStatus::Paid]);
                                }
                            }),
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
