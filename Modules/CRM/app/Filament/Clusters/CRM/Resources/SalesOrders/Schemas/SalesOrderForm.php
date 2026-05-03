<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Enums\SalesOrderStatus;
use Modules\CRM\Enums\SalesOrderType;
use Modules\CRM\Models\CooperationAgreement;
use Modules\CRM\Models\PurchaseOrder;
use Modules\CRM\Models\WorkOrder;
use Modules\MasterData\Models\Tax;
use Modules\Project\Models\Project;

class SalesOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('snapshot'),
                Wizard::make([
                    Step::make('General Information')
                        ->description('Basic project details, reference documents, and document numbering.')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('number')
                                        ->label('SO Number')
                                        ->placeholder('e.g. GDPS/UB/SO-001/26')
                                        ->helperText('The unique identifier for this Sales Order. Automatically generated upon creation.')
                                        ->hidden(fn (string $operation): bool => $operation === 'create')
                                        ->disabled()
                                        ->unique(ignoreRecord: true),
                                    DatePicker::make('order_date')
                                        ->label('Order Date')
                                        ->required()
                                        ->placeholder('Select order date')
                                        ->default(now())
                                        ->helperText('The date this Sales Order is officially recorded.'),
                                    Select::make('project_id')
                                        ->label('Project Reference')
                                        ->relationship('project', 'number')
                                        ->placeholder('Search or select a project...')
                                        ->helperText('Connect this SO to an existing project to retrieve financial and staffing data.')
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if (! $state) {
                                                return;
                                            }
                                            $project = Project::find($state);
                                            if ($project) {
                                                $set('customer_id', $project->customer_id);

                                                $proposal = $project->lead?->proposals()->where('status', ProposalStatus::Approved)->first();
                                                if ($proposal) {
                                                    $set('proposal_id', $proposal->id);
                                                    $set('amount', $proposal->amount);
                                                }

                                                $analysis = $project->profitabilityAnalysis;
                                                if ($analysis) {
                                                    $mfRate = (float) ($analysis->management_fee_rate ?? 0);
                                                    $calculateRevenue = function ($cost) use ($mfRate) {
                                                        return round($cost * (1 + ($mfRate / 100)), 0);
                                                    };

                                                    $manpower = $analysis->manpower_requirements ?? [];
                                                    $financials = $analysis->financial_assumptions ?? [];
                                                    $totalHC = collect($manpower)->sum('quantity');

                                                    // 1. Standardize Manpower
                                                    $standardizedManpower = collect($manpower)->map(function ($mp) use ($calculateRevenue) {
                                                        $unitPrice = $calculateRevenue($mp['unit_cost'] ?? 0);

                                                        return array_merge($mp, [
                                                            'name' => $mp['job_position_name'] ?? 'Personnel',
                                                            'unit_price' => $unitPrice,
                                                            'total_price' => $unitPrice * ($mp['quantity'] ?? 0),
                                                            'type' => 'manpower',
                                                        ]);
                                                    })->toArray();

                                                    // 2. Standardize Operational
                                                    $standardizedOperational = collect($financials['operational_costs'] ?? [])->map(function ($item) use ($calculateRevenue) {
                                                        $unitPrice = $calculateRevenue($item['unit_cost'] ?? 0);

                                                        return array_merge($item, [
                                                            'name' => $item['item_name'] ?? 'Item',
                                                            'unit_price' => $unitPrice,
                                                            'total_price' => $unitPrice * ($item['quantity'] ?? 0),
                                                            'type' => 'operational',
                                                        ]);
                                                    })->toArray();

                                                    // 3. Create Unified Snapshot
                                                    $snapshot = [
                                                        'groups' => [
                                                            'manpower' => $standardizedManpower,
                                                            'operational' => $standardizedOperational,
                                                        ],
                                                        'meta' => [
                                                            'pa_number' => $analysis->number,
                                                            'pa_revision' => $analysis->revision_number ?? 0,
                                                            'management_fee_rate' => $mfRate,
                                                        ],
                                                        'summary' => [
                                                            'manpower_total' => collect($standardizedManpower)->sum('total_price'),
                                                            'operational_total' => collect($standardizedOperational)->sum('total_price'),
                                                        ],
                                                    ];

                                                    // Update UI states
                                                    $set('manpower_initial_qty', $totalHC);
                                                    $set('management_fee_percentage', $mfRate);
                                                    $set('tax_percentage', (string) (float) ($analysis->tax?->rate ?? 12));
                                                    $set('manpower_composition', $standardizedManpower);
                                                    $set('snapshot', $snapshot);

                                                    $set('content_config', array_merge($get('content_config') ?? [], [
                                                        'items' => $standardizedOperational, // Still used by some UI/PDF
                                                        'manpower_details' => $standardizedManpower,
                                                        'pa_revision_number' => $analysis->revision_number ?? 0,
                                                    ]));
                                                }

                                                $lead = $project->lead;
                                                if ($lead) {
                                                    $set('sales_pic_id', $lead->ams_id);
                                                    $set('project_manager_id', $lead->oprep_id);
                                                    if (isset($analysis)) {
                                                        $set('service_type', $analysis->productCluster?->name ?? $lead->service_type);
                                                        $set('job_location', $analysis->projectArea?->name ?? $lead->job_location);
                                                    } else {
                                                        $set('service_type', $lead->service_type);
                                                    }
                                                }

                                                if ($project->sourceable_id) {
                                                    $set('sourceable_id', $project->sourceable_id);
                                                    $set('sourceable_type', $project->sourceable_type);
                                                }
                                            }
                                        }),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    Select::make('customer_id')
                                        ->relationship('customer', 'name')
                                        ->placeholder('Customer will be auto-filled...')
                                        ->required()
                                        ->disabled()
                                        ->dehydrated(),
                                    Select::make('proposal_id')
                                        ->relationship('proposal', 'number')
                                        ->placeholder('Proposal will be auto-filled...')
                                        ->required()
                                        ->disabled()
                                        ->dehydrated(),
                                ]),
                            Grid::make(3)
                                ->schema([
                                    Select::make('type')
                                        ->options(SalesOrderType::class)
                                        ->default(SalesOrderType::External)
                                        ->required()
                                        ->live(),
                                    Select::make('sourceable_type')
                                        ->label('Source Document Type')
                                        ->options([
                                            PurchaseOrder::class => 'Purchase Order (PO)',
                                            WorkOrder::class => 'Work Order (SPK)',
                                            CooperationAgreement::class => 'Cooperation Agreement (PKS)',
                                        ])
                                        ->live()
                                        ->placeholder('Select type')
                                        ->visible(fn (Get $get) => $get('type') === SalesOrderType::Internal)
                                        ->required(fn (Get $get) => $get('type') === SalesOrderType::Internal)
                                        ->afterStateUpdated(fn ($set) => $set('sourceable_id', null)),
                                    Select::make('sourceable_id')
                                        ->label('Source Document')
                                        ->placeholder('Select document')
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->options(function (Get $get) {
                                            $type = $get('sourceable_type');
                                            $customerId = $get('customer_id');
                                            if (! $type) {
                                                return [];
                                            }

                                            return $type::query()
                                                ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
                                                ->get()
                                                ->pluck('number', 'id');
                                        })
                                        ->visible(fn (Get $get) => $get('type') === SalesOrderType::Internal && filled($get('sourceable_type')))
                                        ->required(fn (Get $get) => $get('type') === SalesOrderType::Internal)
                                        ->hintAction(
                                            Action::make('createSource')
                                                ->label('Create')
                                                ->icon(Heroicon::Plus)
                                                ->color('success')
                                                ->tooltip('Auto-create source document from SO data')
                                                ->requiresConfirmation()
                                                ->modalHeading('Auto-create Source Document?')
                                                ->modalDescription('System will generate a new document using current SO amount and service type.')
                                                ->modalSubmitActionLabel('Yes, Create')
                                                ->action(function (Get $get, Set $set) {
                                                    $type = $get('sourceable_type');
                                                    $customerId = $get('customer_id');
                                                    $amount = $get('amount') ?? 0;
                                                    $serviceType = $get('service_type') ?? 'Service from Sales Order';

                                                    if (! $type || ! $customerId) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title('Missing Information')
                                                            ->body('Please ensure Customer and Document Type are selected.')
                                                            ->danger()
                                                            ->send();

                                                        return;
                                                    }

                                                    $recordData = [
                                                        'customer_id' => $customerId,
                                                        'amount' => $amount,
                                                        'is_manual' => true,
                                                        'items' => [
                                                            [
                                                                'item_name' => $serviceType,
                                                                'quantity' => 1,
                                                                'uom' => 'Lot',
                                                                'unit_price' => $amount,
                                                                'total_price' => $amount,
                                                            ],
                                                        ],
                                                    ];

                                                    if ($type === CooperationAgreement::class) {
                                                        $recordData['agreement_date'] = now();
                                                    } else {
                                                        $recordData['order_date'] = now();
                                                    }

                                                    $record = $type::create($recordData);

                                                    // Automatically select the new record
                                                    $set('sourceable_id', $record->id);

                                                    \Filament\Notifications\Notification::make()
                                                        ->title('Document Created')
                                                        ->body("A new record has been generated: {$record->number}")
                                                        ->success()
                                                        ->send();
                                                })
                                        ),
                                ]),
                        ]),
                    Step::make('Execution & Staffing')
                        ->description('Personnel and project execution location.')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('sales_pic_id')
                                        ->label('Sales PIC (AMS)')
                                        ->placeholder('Select Sales Account Management...')
                                        ->relationship('salesPic', 'name')
                                        ->searchable(),
                                    Select::make('project_manager_id')
                                        ->label('Project Manager (Oprep)')
                                        ->placeholder('Select Project Manager...')
                                        ->relationship('projectManager', 'name')
                                        ->searchable(),
                                    TextInput::make('service_type')
                                        ->label('Service Category')
                                        ->placeholder('e.g. Manpower Supply, Cleaning Service')
                                        ->helperText('The primary classification of services provided under this contract.')
                                        ->live(),
                                    TextInput::make('job_location')
                                        ->label('Execution Location')
                                        ->placeholder('e.g. Soekarno-Hatta Airport')
                                        ->helperText('The specific site or area where the project will be executed.'),
                                    TextInput::make('manpower_initial_qty')
                                        ->label('Total Personnel (HC)')
                                        ->numeric()
                                        ->placeholder('0')
                                        ->helperText('The total number of personnel committed in the contract.')
                                        ->default(0),

                                    Hidden::make('manpower_composition')
                                        ->live(),
                                    Hidden::make('content_config')
                                        ->live(),

                                    TextEntry::make('manpower_composition_preview')
                                        ->label('Personnel Deployment Overview')
                                        ->columnSpanFull()
                                        ->state(function (Get $get) {
                                            $manpower = $get('content_config.manpower_details') ?? [];
                                            if (empty($manpower)) {
                                                return new HtmlString('<div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-500 italic text-center border border-dashed border-gray-300">No manpower data available. Please select a Project first.</div>');
                                            }

                                            $totalQty = 0;
                                            $totalAmount = 0;
                                            $rows = collect($manpower)->map(function ($item) use (&$totalQty, &$totalAmount) {
                                                $pos = $item['job_position_name'] ?? 'Unknown Member';
                                                $qty = (int) ($item['quantity'] ?? 0);
                                                $price = (float) ($item['unit_price'] ?? 0);
                                                $total = (float) ($item['total_price'] ?? ($qty * $price));

                                                $totalQty += $qty;
                                                $totalAmount += $total;

                                                return "
                                                <tr class='hover:bg-gray-50/50 transition-colors'>
                                                    <td class='px-4 py-2 border-b border-gray-100 text-sm font-medium text-gray-900'>{$pos}</td>
                                                    <td class='px-4 py-2 border-b border-gray-100 text-sm text-gray-600 text-right'>".number_format($qty)."</td>
                                                    <td class='px-4 py-2 border-b border-gray-100 text-sm text-gray-600 text-right'>".number_format($price, 0, ',', '.')."</td>
                                                    <td class='px-4 py-2 border-b border-gray-100 text-sm font-semibold text-gray-900 text-right'>".number_format($total, 0, ',', '.').'</td>
                                                </tr>';
                                            })->implode('');

                                            return new HtmlString("
                                                <div class='overflow-hidden rounded-xl border border-gray-200 shadow-sm'>
                                                    <table class='w-full text-left bg-white'>
                                                        <thead>
                                                            <tr class='bg-gray-50/80'>
                                                                <th class='px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200'>Position / Rank</th>
                                                                <th class='px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right border-b border-gray-200'>Qty</th>
                                                                <th class='px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right border-b border-gray-200'>Unit Price</th>
                                                                <th class='px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right border-b border-gray-200'>Total Monthly</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>{$rows}</tbody>
                                                        <tfoot class='bg-gray-50/30'>
                                                            <tr class='font-bold bg-blue-50/30'>
                                                                <td class='px-4 py-3 text-sm text-gray-900 uppercase italic font-bold'>Total Staffing Revenue</td>
                                                                <td class='px-4 py-3 text-sm text-gray-900 text-right'>".number_format($totalQty)."</td>
                                                                <td class='px-4 py-3'></td>
                                                                <td class='px-4 py-3 text-sm text-blue-700 text-right font-black'>IDR ".number_format($totalAmount, 0, ',', '.').'</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            ');
                                        })
                                        ->html(),
                                    TextEntry::make('manpower_attachment_preview')
                                        ->label('Original Manpower Budget File')
                                        ->columnSpanFull()
                                        ->visible(function (Get $get) {
                                            $projectId = $get('project_id');
                                            if (! $projectId) {
                                                return false;
                                            }
                                            $pa = Project::find($projectId)?->profitabilityAnalysis;

                                            return $pa && (bool) $pa->is_manual_cost;
                                        })
                                        ->state(function (Get $get) {
                                            $projectId = $get('project_id');
                                            if (! $projectId) {
                                                return null;
                                            }
                                            $pa = Project::find($projectId)?->profitabilityAnalysis;
                                            if (! $pa) {
                                                return 'PA record not found.';
                                            }
                                            $media = $pa->getFirstMedia('manpower_costing_backup');

                                            return $media
                                                ? new HtmlString("<a href='{$media->getTemporaryUrl(now()->addMinutes(60))}' target='_blank' class='text-primary-600 font-bold underline flex items-center gap-2'>
                                                    <svg class='w-4 h-4 inline-block' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'></path></svg> 
                                                    Download Original Manpower Costing ({$media->file_name})
                                                  </a>")
                                                : 'No manpower attachment found.';
                                        })
                                        ->html(),
                                ]),
                        ]),
                    Step::make('Item Details')
                        ->description('Cost and manpower details retrieved from Profitability Analysis.')
                        ->schema([
                            Section::make('Source Documents (Manual Mode)')
                                ->description('Manual calculation files uploaded to Profitability Analysis.')
                                ->visible(function (Get $get) {
                                    $projectId = $get('project_id');
                                    if (! $projectId) {
                                        return false;
                                    }
                                    $pa = Project::find($projectId)?->profitabilityAnalysis;

                                    return $pa && (bool) $pa->is_manual_cost;
                                })
                                ->schema([
                                    TextEntry::make('operational_attachment_preview')
                                        ->label('Backup Costing Tools & Equipment')
                                        ->state(function (Get $get) {
                                            $projectId = $get('project_id');
                                            if (! $projectId) {
                                                return null;
                                            }
                                            $pa = Project::find($projectId)?->profitabilityAnalysis;
                                            if (! $pa) {
                                                return 'PA record not found.';
                                            }
                                            $media = $pa->getFirstMedia('operational_costing_backup');

                                            return $media
                                                ? new HtmlString("<a href='{$media->getTemporaryUrl(now()->addMinutes(60))}' target='_blank' class='text-primary-600 font-bold underline flex items-center gap-2'>
                                                    <svg class='w-4 h-4 inline-block' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'></path></svg> 
                                                    Download Original Tools costing ({$media->file_name})
                                                  </a>")
                                                : 'No tools/equipment attachment found.';
                                        })
                                        ->html(),
                                ]),
                            Section::make('Project Costs')
                                ->description('Details of project cost components. Automatically retrieved from PA.')
                                ->visible(function (Get $get) {
                                    $projectId = $get('project_id');

                                    return (bool) Project::find($projectId)?->profitabilityAnalysis;
                                })
                                ->schema([
                                    TextEntry::make('items_details_preview')
                                        ->label('Operational Cost Details')
                                        ->state(function (Get $get) {
                                            $items = $get('content_config.items') ?? [];
                                            if (empty($items)) {
                                                return new HtmlString('<div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-500 italic text-center border border-dashed border-gray-300">No operational items found. Please select a Project first.</div>');
                                            }

                                            $totalAmount = 0;
                                            $rows = collect($items)->map(function ($item) use (&$totalAmount) {
                                                $desc = $item['description'] ?? 'No Description';
                                                $uom = $item['uom'] ?? 'Unit';
                                                $qty = (float) ($item['quantity'] ?? 0);
                                                $price = (float) ($item['unit_price'] ?? 0);
                                                $total = (float) ($item['total_price'] ?? ($qty * $price));
                                                $totalAmount += $total;

                                                return "
                                                <tr class='hover:bg-gray-50/50 transition-colors'>
                                                    <td class='px-4 py-2 border-b border-gray-100 text-sm font-medium text-gray-900'>{$desc}</td>
                                                    <td class='px-4 py-2 border-b border-gray-100 text-sm text-gray-600 text-right'>".number_format($qty)."</td>
                                                    <td class='px-4 py-2 border-b border-gray-100 text-sm text-gray-600'>{$uom}</td>
                                                    <td class='px-4 py-2 border-b border-gray-100 text-sm text-gray-600 text-right'>".number_format($price, 0, ',', '.')."</td>
                                                    <td class='px-4 py-2 border-b border-gray-100 text-sm font-semibold text-gray-900 text-right'>".number_format($total, 0, ',', '.').'</td>
                                                </tr>';
                                            })->implode('');

                                            return new HtmlString("
                                                <div class='overflow-hidden rounded-xl border border-gray-200 shadow-sm'>
                                                    <table class='w-full text-left bg-white'>
                                                        <thead>
                                                            <tr class='bg-gray-50/80'>
                                                                <th class='px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200'>Description</th>
                                                                <th class='px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right border-b border-gray-200'>Qty</th>
                                                                <th class='px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200'>UoM</th>
                                                                <th class='px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right border-b border-gray-200'>Price</th>
                                                                <th class='px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right border-b border-gray-200'>Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>{$rows}</tbody>
                                                        <tfoot class='bg-gray-50/30'>
                                                            <tr class='font-bold bg-blue-50/30'>
                                                                <td colspan='4' class='px-4 py-3 text-sm text-gray-900 uppercase'>Total Project Costs</td>
                                                                <td class='px-4 py-3 text-sm text-blue-700 text-right font-black'>IDR ".number_format($totalAmount, 0, ',', '.').'</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            ');
                                        })
                                        ->html(),
                                ]),
                        ]),
                    Step::make('Financials & Terms')
                        ->description('Financial targets and payment terms.')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('amount')
                                        ->label('Monthly Gross Amount')
                                        ->placeholder('0')
                                        ->helperText('The total monthly revenue (before tax) for this project.')
                                        ->numeric()
                                        ->prefix('IDR')
                                        ->required()
                                        ->live(),
                                    TextInput::make('management_fee_percentage')
                                        ->label('Management Fee (%)')
                                        ->numeric()
                                        ->placeholder('10')
                                        ->suffix('%')
                                        ->helperText('The agreed fee percentage for managing the project.')
                                        ->default(10),
                                    Select::make('tax_id')
                                        ->label('Tax Scheme (VAT/PPh)')
                                        ->relationship('tax', 'name', fn ($query) => $query->where('category', 'sales')->where('is_active', true))
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(fn ($state, Set $set) => $set('tax_percentage', Tax::find($state)?->rate ?? 0))
                                        ->default(fn () => Tax::where('category', 'sales')->where('is_default', true)->first()?->id),
                                    TextInput::make('tax_percentage')
                                        ->label('Tax Rate (%)')
                                        ->numeric()
                                        ->readOnly()
                                        ->default(fn () => Tax::where('category', 'sales')->where('is_default', true)->first()?->rate ?? 12.00),
                                ]),
                            Section::make('PDF Display Settings')
                                ->description('Customize labels and display options for the generated PDF document.')
                                ->collapsible()
                                ->compact()
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('content_config.vat_label')
                                                ->label('VAT Label (PDF)')
                                                ->placeholder('e.g. PPN')
                                                ->default('PPN'),
                                            TextInput::make('content_config.subtotal_label')
                                                ->label('Subtotal Label (PDF)')
                                                ->placeholder('e.g. Sub Total')
                                                ->default('Sub Total'),
                                            TextInput::make('content_config.total_label')
                                                ->label('Total Label (PDF)')
                                                ->placeholder('e.g. Grand Total')
                                                ->default('Grand Total'),
                                        ]),
                                ]),
                            Section::make('Contractual Terms')
                                ->description('Administrative terms and personnel replacement SLA.')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Textarea::make('payment_terms')
                                                ->label('Terms of Payment')
                                                ->placeholder('e.g. Monthly, 30 Days TOP after invoice')
                                                ->helperText('Agreed payment system and scheduling.')
                                                ->rows(3),
                                            TextInput::make('probation_period')
                                                ->placeholder('e.g. 3 Months')
                                                ->default('3 Months'),
                                            TextInput::make('replacement_sla')
                                                ->label('Replacement SLA')
                                                ->placeholder('e.g. 3 Working Days')
                                                ->default('3 Working Days'),
                                            TextInput::make('reporting_schedule')
                                                ->placeholder('e.g. Every 25th of month')
                                                ->default('5th of each month'),
                                        ]),
                                ]),
                            Section::make('Official Document Attachments')
                                ->description('Manage draft proposals and final signed contract documents. Note: Uploading the signed document will automatically activate the Sales Order.')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            SpatieMediaLibraryFileUpload::make('draft_so')
                                                ->collection('draft_so')
                                                ->label(fn (Get $get) => $get('type') === SalesOrderType::Internal
                                                    ? 'Internal Memo / ST / SPK (Internal)'
                                                    : 'Draft SO / Proposal Document'
                                                )
                                                ->placeholder('Click or drag file here...')
                                                ->required(false)
                                                ->helperText(fn (Get $get) => $get('type') === SalesOrderType::Internal
                                                    ? 'Upload the approved internal document. This will automatically approve the Sales Order.'
                                                    : 'Internal review version or initial proposal (Optional).'
                                                ),

                                            SpatieMediaLibraryFileUpload::make('signed_so')
                                                ->collection('signed_so')
                                                ->label(fn (Get $get) => $get('type') === SalesOrderType::Internal
                                                    ? 'Approved Internal Memo / ST / PO (Scan)'
                                                    : 'Signed SO / SPK / PO (Scan)'
                                                )
                                                ->placeholder('Click or drag file here...')
                                                ->visible(fn (Get $get) => $get('status') !== SalesOrderStatus::Draft->value)
                                                ->required(false)
                                                ->helperText('Final signed document. Uploading this will automatically set the SO status to Approved.'),
                                        ]),
                                ]),
                        ]),
                ])
                    ->disabled(fn ($record) => $record && $record->status === SalesOrderStatus::Approved)
                    ->columnSpanFull(),
            ]);
    }
}
