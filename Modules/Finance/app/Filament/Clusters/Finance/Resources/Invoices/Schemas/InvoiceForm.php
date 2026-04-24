<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
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
use Modules\CRM\Models\SalesOrder;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Project\Models\WorkCompletionReport;
use Modules\MasterData\Enums\Gender;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice Identification')
                    ->description('Define the core identity and dates for this invoice.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g. INV/2024/001')
                            ->helperText('Leave blank or enter "Auto-generated" to let the system sequence it.'),
                        DatePicker::make('invoice_date')
                            ->label('Invoice Date')
                            ->required()
                            ->default(now())
                            ->helperText('The date this invoice is officially issued.'),
                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required()
                            ->default(now()->addDays(30))
                            ->helperText('The deadline for the customer to complete payment.'),
                        Hidden::make('status')
                            ->default(InvoiceStatus::Draft),
                    ])->columns(2),

                Section::make('Related Documents')
                    ->description('Link this invoice to its source documents.')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->placeholder('Select a customer...')
                            ->helperText('The client being billed.'),
                        Select::make('sales_order_id')
                            ->label('Sales Order')
                            ->options(SalesOrder::all()->pluck('so_number', 'id'))
                            ->searchable()
                            ->live()
                            ->placeholder('Select a sales order...')
                            ->helperText('The originating contract or PO.'),
                        Select::make('work_completion_report_id')
                            ->label('Work Completion Report (BAPP)')
                            ->options(WorkCompletionReport::all()->pluck('report_number', 'id'))
                            ->searchable()
                            ->live()
                            ->placeholder('Select a BAPP...')
                            ->helperText('The finalized service delivery proof.')
                            ->afterStateUpdated(function ($state, $set) {
                                if (! $state) return;
                                
                                $bapp = WorkCompletionReport::find($state);
                                if ($bapp) {
                                    $set('customer_id', $bapp->customer_id);
                                    $set('sales_order_id', $bapp->sales_order_id);
                                    $set('content_config.recipient_name', $bapp->content_config['recipient_name'] ?? null);
                                    $set('content_config.recipient_title', $bapp->content_config['recipient_title'] ?? null);
                                    $set('content_config.recipient_gender', $bapp->content_config['recipient_gender'] ?? null);
                                    
                                    if (!empty($bapp->items)) {
                                        $set('items', $bapp->items);
                                        
                                        // Auto-calculate totals
                                        $sum = 0;
                                        foreach ((array) $bapp->items as $item) {
                                            $sum += floatval($item['total_price'] ?? 0);
                                        }
                                        $set('amount', $sum);
                                        $tax = $sum * 0.11;
                                        $set('tax_amount', $tax);
                                        $set('total_amount', $sum + $tax);
                                    }
                                }
                            }),
                    ])->columns(3),

                Section::make('Customer Signatory')
                    ->description('Select or manually enter the person who will receive/sign this invoice from the customer side.')
                    ->schema([
                        Select::make('recipient_contact_index')
                            ->label('Customer Contact Reference')
                            ->options(function (Get $get) {
                                $customerId = $get('customer_id');
                                if (!$customerId) {
                                    return [];
                                }
                                $customer = Customer::find($customerId);
                                if (!$customer || empty($customer->contacts)) {
                                    return [];
                                }
                                return collect($customer->contacts)
                                    ->mapWithKeys(fn ($contact, $index) => [$index => $contact['name'] . ' (' . ($contact['position'] ?? $contact['job_position'] ?? 'No Position') . ')'])
                                    ->toArray();
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if ($state === null || $state === '') {
                                    return;
                                }
                                $customerId = $get('customer_id');
                                if (!$customerId) {
                                    return;
                                }
                                $customer = Customer::find($customerId);
                                if (!$customer || empty($customer->contacts)) {
                                    return;
                                }
                                $contact = $customer->contacts[$state] ?? null;
                                if ($contact) {
                                    $set('content_config.recipient_name', $contact['name'] ?? '');
                                    $position = $contact['position'] ?? $contact['job_position'] ?? $contact['job_title'] ?? $contact['title'] ?? '';
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
                                    TextInput::make('job_position')->label('Job Position'),
                                ]),
                                Grid::make(2)->schema([
                                    TextInput::make('email')->email(),
                                    TextInput::make('phone')->tel(),
                                ]),
                            ])
                            ->createOptionUsing(function (array $data, Get $get) {
                                $customerId = $get('customer_id');
                                if (!$customerId) {
                                    return null;
                                }
                                $customer = Customer::find($customerId);
                                if (!$customer) {
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
                            ->dehydrated(false)
                            ->helperText('Selecting a contact will populate the fields below.'),

                        Grid::make(3)->schema([
                            Select::make('content_config.recipient_gender')
                                ->label('Salutation')
                                ->options(Gender::class)
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
                                            ->afterStateUpdated(fn ($get, $set) => $set('total_price', floatval($get('quantity') ?? 0) * floatval(str_replace('.', '', $get('unit_price') ?? 0)))),
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
                                            ->afterStateUpdated(fn ($get, $set) => $set('total_price', floatval($get('quantity') ?? 0) * floatval(str_replace('.', '', $get('unit_price') ?? 0)))),
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
                            ->afterStateUpdated(function ($state, $set) {
                                // Auto-calculate amount when items change
                                $sum = 0;
                                foreach ((array) $state as $item) {
                                    $sum += floatval($item['total_price'] ?? 0);
                                }
                                $set('amount', $sum);
                                $tax = $sum * 0.11;
                                $set('tax_amount', $tax);
                                $set('total_amount', $sum + $tax);
                            }),
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
                                    ->afterStateUpdated(function ($state, $set) {
                                        $amount = (float) str_replace('.', '', $state);
                                        $tax = $amount * 0.11;
                                        $set('tax_amount', $tax);
                                        $set('total_amount', $amount + $tax);
                                    }),
                                TextInput::make('tax_amount')
                                    ->label('Tax (11%)')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->placeholder('0')
                                    ->helperText('Value Added Tax (PPN). Auto-calculated at 11%.')
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
                            ]),
                    ]),

                Section::make('Payment Information')
                    ->description('Specify the banking details for remittance.')
                    ->schema([
                        TextInput::make('payment_info.account_name')
                            ->label('Account Name (A/N)')
                            ->required()
                            ->placeholder('e.g. PT. Garuda Daya Pratama Sejahtera')
                            ->helperText('The registered entity name for the bank accounts.'),
                        Repeater::make('payment_info.banks')
                            ->label('Bank Accounts')
                            ->addActionLabel('Add Bank Account')
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label('Bank Name')
                                    ->required()
                                    ->placeholder('e.g. Bank Mandiri'),
                                TextInput::make('account_number')
                                    ->label('Account Number')
                                    ->required()
                                    ->placeholder('e.g. 155-00-1307311-2'),
                                TextInput::make('currency')
                                    ->label('Currency')
                                    ->required()
                                    ->placeholder('e.g. IDR'),
                            ])
                            ->columns(3),
                    ]),

                Section::make('Attachments')
                    ->description('Upload physical or digital documents related to this invoice.')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('draft_invoice')
                            ->collection('draft_invoice')
                            ->label('Draft / Signed Invoice PDF')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->helperText('Upload the drafted or finalized invoice document (PDF only, max 10MB).'),
                        SpatieMediaLibraryFileUpload::make('payment_proof')
                            ->collection('payment_proof')
                            ->label('Payment Proof (Bukti Bayar)')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(5120)
                            ->helperText('Upload the official transfer receipt from the client (PDF/JPG/PNG, max 5MB).'),
                    ])->columns(2),
            ]);
    }
}
