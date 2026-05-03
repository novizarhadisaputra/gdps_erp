<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectInformations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\BillingOption;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\PaymentTerm;
use Modules\MasterData\Models\ProjectType;

class ProjectInformationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->schema([
                        TextInput::make('document_number')
                            ->label('PI Number')
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('project.number')
                            ->label('Project Number')
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('project.name')
                            ->label('Project Name')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                        Select::make('project_type_id')
                            ->label('Project Type')
                            ->options(ProjectType::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        DatePicker::make('start_date')
                            ->required(),
                        DatePicker::make('end_date')
                            ->required(),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Commercial Details')
                    ->schema([
                        TextInput::make('revenue_per_month')
                            ->numeric()
                            ->prefix('IDR')
                            ->required(),
                        TextInput::make('management_fee_per_month')
                            ->numeric()
                            ->prefix('IDR'),
                        TextInput::make('direct_cost')
                            ->numeric()
                            ->prefix('IDR'),
                        TextInput::make('ppn_percentage')
                            ->numeric()
                            ->suffix('%')
                            ->default(12),
                        Select::make('payment_term_id')
                            ->label('Payment Term')
                            ->options(PaymentTerm::all()->pluck('name', 'id'))
                            ->searchable(),
                        Select::make('billing_option_id')
                            ->label('Billing Option')
                            ->options(BillingOption::all()->pluck('name', 'id'))
                            ->searchable(),
                    ])->columns(2),

                Section::make('Assignments')
                    ->schema([
                        Select::make('oprep_id')
                            ->label('Operational Representative')
                            ->options(Employee::all()->pluck('name', 'id'))
                            ->searchable(),
                        Select::make('ams_id')
                            ->label('Account Manager/Sales')
                            ->options(Employee::all()->pluck('name', 'id'))
                            ->searchable(),
                    ])->columns(2),

                Section::make('Operational Deadlines')
                    ->schema([
                        DatePicker::make('bapp_cut_off_date')
                            ->label('BAPP Cut-off Date'),
                        DatePicker::make('max_invoice_send_date')
                            ->label('Max Invoice Send Date'),
                        DatePicker::make('payroll_date')
                            ->label('Payroll Date'),
                        DatePicker::make('overtime_cut_off_date')
                            ->label('Overtime Cut-off Date'),
                    ])->columns(2),

                Section::make('Additional Information')
                    ->schema([
                        TextInput::make('operational_visit_schedule')
                            ->label('Operational Visit Schedule'),
                        Select::make('ipk_status')
                            ->label('IPK Status')
                            ->options([
                                'eligible' => 'Eligible',
                                'not_eligible' => 'Not Eligible',
                            ]),
                        Select::make('thr_status')
                            ->label('THR Status')
                            ->options([
                                'eligible' => 'Eligible',
                                'not_eligible' => 'Not Eligible',
                            ]),
                        TextInput::make('previous_code')
                            ->label('Previous Project Code'),
                        Textarea::make('remarks')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Remuneration Details')
                    ->schema([
                        Repeater::make('remuneration_details')
                            ->schema([
                                Select::make('job_position_id')
                                    ->label('Position')
                                    ->relationship('jobPosition', 'name')
                                    ->options(JobPosition::all()->pluck('name', 'id'))
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->disabled(),
                                TextInput::make('unit_cost')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->disabled(),
                                TextInput::make('total_monthly_cost')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->disabled(),
                            ])
                            ->columns(4)
                            ->addable(false)
                            ->deletable(false),
                    ]),

                Section::make('Operational & Analysis Details')
                    ->schema([
                        Repeater::make('analysis_details.operational_costs')
                            ->label('Operational Costs')
                            ->schema([
                                TextInput::make('item_name')
                                    ->label('Item')
                                    ->disabled(),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->disabled(),
                                TextInput::make('unit_cost')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->disabled(),
                                TextInput::make('total_monthly_cost')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->disabled(),
                            ])
                            ->columns(4)
                            ->addable(false)
                            ->deletable(false),
                    ]),
            ]);
    }
}
