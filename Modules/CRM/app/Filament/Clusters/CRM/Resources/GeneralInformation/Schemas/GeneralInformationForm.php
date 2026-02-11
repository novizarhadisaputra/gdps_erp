<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\CRM\Models\SalesPlan;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles\Schemas\ContactRoleForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Schemas\CustomerForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Models\ContactRole;
use Modules\MasterData\Models\Customer;
use Modules\MasterData\Models\ProjectArea;

class GeneralInformationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('General Details')
                ->schema([
                    TextInput::make('document_number')
                        ->label('Document Number')
                        ->disabled() // Always auto-generated/disabled
                        ->dehydrated(false) // Usually not manually input
                        ->hiddenOn('create'),
                    Select::make('lead_id')
                        ->relationship('lead', 'title')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required()
                        ->disabled(fn ($state) => filled($state))
                        ->dehydrated()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (! $state) {
                                return;
                            }
                            $lead = \Modules\CRM\Models\Lead::find($state);
                            if (! $lead) {
                                return;
                            }
                            $set('customer_id', $lead->customer_id);
                            $set('scope_of_work', $lead->title);
                            $set('description', $lead->description);
                        }),
                    Select::make('sales_plan_id')
                        ->label('Sales Plan (Basis)')
                        ->relationship('salesPlan', 'id', fn ($query, $get) => $query->where('lead_id', $get('../../lead_id') ?? $get('lead_id')))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (! $state) {
                                return;
                            }

                            $plan = SalesPlan::find($state);
                            if (! $plan) {
                                return;
                            }

                            $set('estimated_start_date', $plan->start_date);
                            $set('estimated_end_date', $plan->end_date);
                            $set('project_type_id', $plan->project_type_id);
                        }),
                    Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->disabled()
                        ->dehydrated()
                        ->createOptionForm(CustomerForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->createOptionUsing(fn (array $data) => Customer::create($data)->id)
                        ->editOptionForm(CustomerForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),

                    // Status removed from form as per request
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Person In Charge (PIC)')
                ->schema([
                    Repeater::make('pics')
                        ->relationship('pics')
                        ->label('Contact Persons')
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Select::make('contact_role_id')
                                ->label('Role / Position')
                                ->relationship('contactRole', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->createOptionForm(ContactRoleForm::schema())
                                ->createOptionAction(fn (Action $action) => $action->slideOver())
                                ->createOptionUsing(fn (array $data) => ContactRole::create($data)->id)
                                ->editOptionForm(ContactRoleForm::schema())
                                ->editOptionAction(fn (Action $action) => $action->slideOver()),
                            TextInput::make('phone')
                                ->tel()
                                ->maxLength(255),
                            TextInput::make('email')
                                ->email()
                                ->maxLength(255),
                        ])
                        ->columns(2)
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                        ->collapsible()
                        ->minItems(1)
                        ->defaultItems(1),
                ])
                ->columnSpanFull(),

            Section::make('Project Details')
                ->schema([
                    Textarea::make('scope_of_work')
                        ->label('Scope of Work')
                        ->helperText('Define the scope of work linked to the agreement.')
                        ->columnSpanFull(),
                    Select::make('project_area_id')
                        ->label('Location')
                        ->relationship('projectArea', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('Location of work execution (Project Area).')
                        ->createOptionForm(ProjectAreaForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->createOptionUsing(fn (array $data) => ProjectArea::create($data)->id)
                        ->editOptionForm(ProjectAreaForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Grid::make(3)
                        ->schema([
                            DatePicker::make('estimated_start_date')
                                ->label('Start Date')
                                ->live()
                                ->afterStateUpdated(fn (Get $get, Set $set) => static::calculateDuration($get, $set)),
                            DatePicker::make('estimated_end_date')
                                ->label('End Date')
                                ->live()
                                ->afterStateUpdated(fn (Get $get, Set $set) => static::calculateDuration($get, $set)),
                            TextInput::make('contract_duration')
                                ->label('Contract Duration')
                                ->helperText('Calculated duration based on start and end dates.')
                                ->placeholder('Auto-calculated')
                                ->suffix('Months')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    Textarea::make('manpower_qualifications')
                        ->label('Manpower Qualifications')
                        ->helperText('General workforce qualifications required.')
                        ->columnSpanFull(),
                    Textarea::make('work_activities')
                        ->label('Work Activities')
                        ->helperText('Specific work activities or tasks.')
                        ->columnSpanFull(),
                    Textarea::make('service_level')
                        ->label('Service Level')
                        ->helperText('Agreed service level agreement (SLA).')
                        ->columnSpanFull(),
                    Textarea::make('billing_requirements')
                        ->label('Reporting & Billing Requirements')
                        ->helperText('Requirements for work reporting and invoicing.')
                        ->columnSpanFull(),
                ])->columnSpanFull(),

            Section::make('Risk Register & Compliance')
                ->schema([
                    TextInput::make('rr_document_number')
                        ->label('Risk Register Document Number')
                        ->placeholder('RR-xxxx-xxxx')
                        ->live() // Make it reactive
                        ->suffixAction(
                            Action::make('check_status')
                                ->icon(fn (Get $get) => filled($get('risk_management')) ? 'heroicon-m-check-circle' : 'heroicon-m-magnifying-glass')
                                ->label('Check Status')
                                ->tooltip('Check Approval Status')
                                ->action(function (Get $get, Set $set, $state) {
                                    // Simulation: Fetch data from Risk Register System
                                    // If approved, populate the risk_management repeater

                                    if (! $state) {
                                        Notification::make()
                                            ->title('Error')
                                            ->body('Please enter a Risk Register Number.')
                                            ->danger()
                                            ->send();

                                        return;
                                    }

                                    Notification::make()
                                        ->title('Synced!')
                                        ->body('Risk Management data retrieved from Document: '.$state)
                                        ->success()
                                        ->send();

                                    // Simulate data sync
                                    $set('risk_management', [
                                        [
                                            'risk_item' => 'Financial Risk (Synced)',
                                            'mitigation' => 'Hedging Strategy (Synced)',
                                        ],
                                        [
                                            'risk_item' => 'Operational Risk (Synced)',
                                            'mitigation' => 'SOP Implementation (Synced)',
                                        ],
                                    ]);
                                })
                        ),

                    Repeater::make('risk_management')
                        ->schema([
                            TextInput::make('risk_item')->required(),
                            TextInput::make('mitigation')->required(),
                        ])
                        ->columns(2)
                        ->columnSpanFull()
                        ->hidden(fn (Get $get) => blank($get('rr_document_number'))),

                    Textarea::make('rr_payload')
                        ->label('Raw RR Webhook Payload')
                        ->helperText('The latest raw JSON data received from the Risk Register system.')
                        ->readOnly()
                        ->rows(10)
                        ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : null)
                        ->columnSpanFull()
                        ->visible(fn ($record) => filled($record?->rr_payload)),
                ])
                ->hiddenOn(operations: ['create'])
                ->columnSpanFull(),

            // Feasibility Study removed as per request

            Section::make('Additional Information')
                ->schema([
                    Textarea::make('description')->columnSpanFull()->rows(3),
                    Textarea::make('remarks')->columnSpanFull()->rows(2),
                ])
                ->columnSpanFull(),

            Section::make('Project Documents')
                ->description('Upload required project documentation.')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('tor')
                                ->collection('tor')
                                ->label('ToR Document')
                                ->disk('s3')
                                ->visibility('private')
                                ->required(),
                            SpatieMediaLibraryFileUpload::make('rfp')
                                ->collection('rfp')
                                ->label('RFP Document')
                                ->disk('s3')
                                ->visibility('private')
                                ->required(),
                            SpatieMediaLibraryFileUpload::make('rfi')
                                ->collection('rfi')
                                ->label('RFI Document')
                                ->disk('s3')
                                ->visibility('private')
                                ->required(),
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }

    public static function calculateDuration(Get $get, Set $set): void
    {
        $start = $get('estimated_start_date');
        $end = $get('estimated_end_date');

        if ($start && $end) {
            $startDate = Carbon::parse($start);
            $endDate = Carbon::parse($end);

            if ($endDate > $startDate) {
                // simple diff in months
                $months = $startDate->diffInMonths($endDate);
                // If it's partial month, maybe round up?
                // diffInMonths returns integer.
                // Let's use floatDiffInMonths if precision needed, but usually integers.
                // Let's stick to integer for now as per "Months" suffix.
                $set('contract_duration', $months);
            } else {
                $set('contract_duration', 0);
            }
        } else {
            $set('contract_duration', null);
        }
    }
}
