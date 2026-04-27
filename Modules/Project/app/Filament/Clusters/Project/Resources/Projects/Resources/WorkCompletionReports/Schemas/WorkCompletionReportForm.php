<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\SalesOrder;
use Modules\MasterData\Enums\Gender;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Models\Project;

class WorkCompletionReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Documents')
                    ->description('Download the draft BAPP to be signed, then upload the final scanned document once signed by all parties to proceed with approval.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('draft_report')
                                    ->label('Draft BAPP (Unsigned)')
                                    ->collection('draft_report')
                                    ->disk('s3')
                                    ->downloadable()
                                    ->openable()
                                    ->helperText('The system-generated draft document that has not yet been signed.'),

                                SpatieMediaLibraryFileUpload::make('signed_report')
                                    ->label('Signed BAPP (Final Scan)')
                                    ->collection('signed_report')
                                    ->disk('s3')
                                    ->downloadable()
                                    ->openable()
                                    ->helperText('Upload the scanned document that has been signed by both parties.')
                                    ->required(fn (Get $get) => 
                                        $get('status') === WorkCompletionStatus::Submitted->value && 
                                        $get('so_type') !== \Modules\CRM\Enums\SalesOrderType::Internal->value
                                    ),
                            ]),
                    ])->columnSpanFull()
                    ->collapsible(),

                Section::make('Report Details')
                    ->schema([
                        TextInput::make('number')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated'),
                        DatePicker::make('document_date')
                            ->required()
                            ->default(now()),
                        Select::make('project_id')
                            ->label('Project')
                            ->options(Project::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])->columns(2)
                    ->collapsible(),

                Section::make('Source Document')
                    ->description('Select the primary document this BAPP is based on.')
                    ->schema([
                        MorphToSelect::make('sourceable')
                            ->label('Reference Document')
                            ->types([
                                MorphToSelect\Type::make(\Modules\CRM\Models\SalesOrder::class)
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
                                MorphToSelect\Type::make(\Modules\Project\Models\WorkCompletionReport::class)
                                    ->titleAttribute('number'),
                            ])
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if (!$state) return;

                                [$modelClass, $id] = explode(':', $state);
                                $source = $modelClass::find($id);

                                if (!$source) return;

                                if (isset($source->customer_id)) {
                                    $set('customer_id', $source->customer_id);
                                }

                                if ($source instanceof \Modules\CRM\Models\SalesOrder) {
                                    $set('so_type', $source->type->value);
                                    
                                    $manpower = $source->content_config['manpower_details'] ?? [];
                                    $operational = $source->content_config['items'] ?? [];
                                    $mfRate = (float) ($source->management_fee_percentage ?? 0);

                                    $calculateRevenue = function ($cost) use ($mfRate) {
                                        if ($mfRate >= 100) return $cost * 1.15;
                                        return $cost / (1 - ($mfRate / 100));
                                    };

                                    $items = [];
                                    foreach ($manpower as $mp) {
                                        $price = $calculateRevenue((float)($mp['unit_cost'] ?? 0));
                                        $items[] = [
                                            'item_name' => $mp['job_position_name'] ?? 'Personnel',
                                            'quantity' => $mp['quantity'] ?? 0,
                                            'uom' => $mp['uom'] ?? 'Person',
                                            'unit_price' => $price,
                                            'total_price' => $price * ($mp['quantity'] ?? 0),
                                            'so_reference' => $source->type === \Modules\CRM\Enums\SalesOrderType::Internal ? '-' : $source->number,
                                        ];
                                    }

                                    foreach ($operational as $op) {
                                        $price = $calculateRevenue((float)($op['unit_cost'] ?? 0));
                                        $items[] = [
                                            'item_name' => $op['item_name'] ?? 'Item',
                                            'quantity' => $op['quantity'] ?? 0,
                                            'uom' => $op['uom'] ?? 'Unit',
                                            'unit_price' => $price,
                                            'total_price' => $price * ($op['quantity'] ?? 0),
                                            'so_reference' => $source->type === \Modules\CRM\Enums\SalesOrderType::Internal ? '-' : $source->number,
                                        ];
                                    }

                                    $set('items', $items);
                                    $set('tax_percentage', $source->type === \Modules\CRM\Enums\SalesOrderType::Internal ? 0 : 11);
                                    $set('tax_wording', $source->type === \Modules\CRM\Enums\SalesOrderType::Internal ? ['id' => '-', 'en' => '-'] : ['id' => 'Pelaksanaan pekerjaan di atas belum termasuk PPN 11%', 'en' => 'The above work execution does not include 11% VAT']);
                                } else {
                                    $set('so_type', 'External');
                                    if (isset($source->items)) {
                                        $set('items', $source->items);
                                    }
                                }
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Customer Signatory')
                    ->description('Select or manually enter the person who will sign this BAPP from the customer side.')
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
                                    ->mapWithKeys(fn ($contact, $index) => [$index => $contact['name'].' ('.($contact['position'] ?? $contact['job_position'] ?? 'No Position').')'])
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

                Section::make('Service & Progress')
                    ->schema([
                        DatePicker::make('service_period_start')
                            ->required(),
                        DatePicker::make('service_period_end')
                            ->required(),
                        TextInput::make('work_progress_percentage')
                            ->numeric()
                            ->suffix('%')
                            ->required()
                            ->default(100),
                        Select::make('status')
                            ->options(WorkCompletionStatus::class)
                            ->required()
                            ->default(WorkCompletionStatus::Draft),
                    ])->columns(2)
                    ->collapsible(),

                Section::make('Additional Information')
                    ->schema([
                        TextInput::make('so_type')
                            ->hidden()
                            ->dehydrated(false),
                        Textarea::make('description')
                            ->columnSpanFull()
                            ->translatable(),
                    ])->columns(2)
                    ->collapsible(),

                Section::make('BAPP Line Items')
                    ->description('Detailed breakdown of work completed based on the Sales Order.')
                    ->schema([
                        Repeater::make('items')
                            ->label('Line Items')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('item_name')
                                            ->label('Item Name')
                                            ->required()
                                            ->columnSpanFull(),
                                        TextInput::make('so_reference')
                                            ->label('SO Reference')
                                            ->disabled(),
                                        TextInput::make('quantity')
                                            ->label('Quantity')
                                            ->numeric()
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($get, $set) => $set('total_price', floatval($get('quantity') ?? 0) * floatval($get('unit_price') ?? 0))),
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
                                            ->afterStateUpdated(fn ($get, $set) => $set('total_price', floatval($get('quantity') ?? 0) * floatval($get('unit_price') ?? 0))),
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
                            ->addActionLabel('Add Manual Entry')
                            ->translatable(),
                    ])->collapsible(),

                Section::make('Total')
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('Grand Total')
                            ->numeric()
                            ->prefix('IDR')
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->readonly()
                            ->live()
                            ->afterStateHydrated(fn ($set, $get) => $set('total_amount', collect($get('items'))->sum('total_price'))),
                    ]),

                Section::make('Tax Configuration')
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('tax_percentage')
                                ->label('PPN (%)')
                                ->numeric()
                                ->default(11)
                                ->suffix('%')
                                ->required()
                                ->live(),
                            TextInput::make('tax_wording')
                                ->label('Tax Statement (Free Text)')
                                ->placeholder('e.g. Pelaksanaan pekerjaan di atas belum termasuk PPN 11%')
                                ->default('Pelaksanaan pekerjaan di atas belum termasuk PPN 11%')
                                ->columnSpan(3)
                                ->required()
                                ->translatable(),
                        ]),
                    ]),
            ]);
    }
}
