<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                            ->createOptionForm(CustomerForm::schema()),
                        TextInput::make('document_number')
                            ->label('Document Number'),
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
                            ->columnSpanFull(),
                        TextInput::make('location'),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('estimated_start_date'),
                                DatePicker::make('estimated_end_date'),
                            ]),
                        Textarea::make('manpower_qualifications')
                            ->columnSpanFull(),
                        Textarea::make('work_activities')
                            ->columnSpanFull(),
                        Textarea::make('service_level')
                            ->columnSpanFull(),
                        Textarea::make('billing_requirements')
                            ->columnSpanFull(),
                    ]),

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
                SpatieMediaLibraryFileUpload::make('feasibility_study_file')
                    ->collection('feasibility_study')
                    ->label('Feasibility Study Document')
                    ->disk('s3')
                    ->visibility('private'),
                SpatieMediaLibraryFileUpload::make('rr_document')
                    ->collection('rr_document')
                    ->label('RR Document')
                    ->disk('s3')
                    ->visibility('private'),
            ]);
    }
}
