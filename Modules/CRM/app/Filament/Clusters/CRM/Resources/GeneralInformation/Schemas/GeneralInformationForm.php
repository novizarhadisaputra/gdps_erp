<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Resources\Customers\Schemas\CustomerForm;

class GeneralInformationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Details')
                    ->schema([
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated()
                            ->createOptionForm(CustomerForm::schema()),
                        TextInput::make('document_number')
                            ->label('Document Number')
                            ->disabled() // Always auto-generated/disabled
                            ->dehydrated(false) // Usually not manually input
                            ->hiddenOn('create'),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Submitted',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('draft'),
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
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Textarea::make('description'),
                                    ]),
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
                        TextInput::make('location')
                            ->label('Location')
                            ->helperText('Location of work execution.'),
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('estimated_start_date')
                                    ->label('Start Date')
                                    ->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDuration($get, $set)),
                                DatePicker::make('estimated_end_date')
                                    ->label('End Date')
                                    ->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDuration($get, $set)),
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

                Repeater::make('risk_management')
                    ->schema([
                        TextInput::make('risk_item')->required(),
                        TextInput::make('mitigation')->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Repeater::make('feasibility_study')
                    ->schema([
                        TextInput::make('item')->required(),
                        TextInput::make('value')->required(),
                        Textarea::make('notes')->rows(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Textarea::make('description')->columnSpanFull()->rows(3),
                Textarea::make('remarks')->columnSpanFull()->rows(2),
                TextInput::make('rr_submission_id')
                    ->label('RR Submission ID')
                    ->disabled()
                    ->dehydrated(false),
                // Feasibility Study and RR Document fields removed

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
            ]);
    }

    public static function calculateDuration(Get $get, Set $set): void
    {
        $start = $get('estimated_start_date');
        $end = $get('estimated_end_date');

        if ($start && $end) {
            $startDate = \Carbon\Carbon::parse($start);
            $endDate = \Carbon\Carbon::parse($end);

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
