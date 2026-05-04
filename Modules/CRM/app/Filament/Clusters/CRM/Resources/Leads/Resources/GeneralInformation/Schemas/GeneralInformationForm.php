<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Enums\Gender;
use Modules\MasterData\Models\ContactRole;

class GeneralInformationForm
{
    public static function schema(): array
    {
        return [
            Section::make('Basic Information')
                ->schema([
                    TextInput::make('number')
                        ->disabled()
                        ->placeholder('Generated after create'),
                    Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'submitted' => 'Submitted',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->required()
                        ->placeholder('Select status')
                        ->default('draft')
                        ->disabled(),
                    Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->placeholder('Select customer')
                        ->default(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords ? $livewire->getOwnerRecord()->customer_id : null),
                    TextInput::make('scope_of_work')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Example: Integrated Security Manpower Procurement')
                        ->columnSpanFull()
                        ->default(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords ? $livewire->getOwnerRecord()->title : null),
                    Textarea::make('description')
                        ->rows(3)
                        ->placeholder('Provide detailed description regarding the work scope or project objectives')
                        ->columnSpanFull()
                        ->default(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords ? $livewire->getOwnerRecord()->description : null),
                ])->columns(2),

            Section::make('Project Context')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            DatePicker::make('estimated_start_date')
                                ->required()
                                ->native(false)
                                ->placeholder('Select start date')
                                ->default(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords ? $livewire->getOwnerRecord()->start_date : null),
                            DatePicker::make('estimated_end_date')
                                ->required()
                                ->native(false)
                                ->placeholder('Select end date')
                                ->default(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords ? $livewire->getOwnerRecord()->end_date : null),
                            Select::make('project_area_id')
                                ->relationship(
                                    name: 'projectArea',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn ($query, Get $get) => $query->whereHas('customers', fn ($q) => $q->where('customers.id', $get('customer_id')))
                                )
                                ->label('Project Area')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->placeholder('Select project area')
                                ->visible(fn (Get $get) => filled($get('customer_id')))
                                ->default(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords ? $livewire->getOwnerRecord()->project_area_id : null),
                            Select::make('work_scheme_id')
                                ->relationship('workScheme', 'name')
                                ->label('Work Scheme')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->helperText('Working pattern for this project.')
                                ->placeholder('Select work scheme'),
                            TextInput::make('location')
                                ->placeholder('Example: Terminal 3 Bandara Soekarno-Hatta'),
                            Select::make('sales_plan_id')
                                ->relationship('salesPlan', 'project_code')
                                ->getOptionLabelFromRecordUsing(fn ($record) => $record?->project_code ?? 'No Project Code')
                                ->label('Source Sales Plan')
                                ->disabled()
                                ->placeholder('Select from Sales Plan list')
                                ->columnSpanFull(),
                        ]),
                ]),

            Section::make('Requirements & Work Details')
                ->description('Technical specifics and operational requirements.')
                ->schema([
                    Textarea::make('manpower_qualifications')
                        ->rows(3)
                        ->required()
                        ->placeholder('Example: Min. height 170cm, Gada Pratama certificate, min. high school education, max age 35 years'),
                    Textarea::make('work_activities')
                        ->rows(3)
                        ->required()
                        ->placeholder('Example: Airport area patrol, personnel ID check, CCTV monitoring, baggage screening'),
                    Textarea::make('service_level')
                        ->rows(3)
                        ->required()
                        ->placeholder('Example: Incident response time < 5 minutes, 100% personnel availability per shift'),
                    Textarea::make('billing_requirements')
                        ->rows(3)
                        ->required()
                        ->placeholder('Example: Invoice sent no later than the 5th of each month, complete BAPP attachments'),
                ])->columns(2),

            Section::make('Documentation')
                ->description('Upload Term of Reference, RFP, and RFQ Documents.')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('tor')
                        ->collection('tor')
                        ->label('ToR Document')
                        ->disk('s3')
                        ->visibility('private'),
                    SpatieMediaLibraryFileUpload::make('rfp')
                        ->collection('rfp')
                        ->label('RFP Document')
                        ->disk('s3')
                        ->visibility('private'),
                    SpatieMediaLibraryFileUpload::make('rfq')
                        ->collection('rfq')
                        ->label('RFQ Document')
                        ->disk('s3')
                        ->visibility('private'),
                    SpatieMediaLibraryFileUpload::make('other_documents')
                        ->collection('other_documents')
                        ->label('Other Documents')
                        ->disk('s3')
                        ->visibility('private')
                        ->multiple()
                        ->downloadable()
                        ->openable()
                        ->helperText('Additional supporting documents (e.g., permits, certifications).'),
                ])->columns(2),

            Section::make('PICs & Remarks')
                ->schema([
                    Repeater::make('pics')
                        ->schema([
                            Select::make('gender')
                                ->label('Gender')
                                ->options(Gender::class)
                                ->placeholder('Select')
                                ->native(false),
                            TextInput::make('name')
                                ->required(),
                            TextInput::make('job_position')
                                ->label('Job Position')
                                ->placeholder('e.g. Procurement Manager'),
                            TextInput::make('phone'),
                            TextInput::make('email')
                                ->email(),
                            Select::make('contact_role_id')
                                ->label('Role (Functional)')
                                ->options(fn () => ContactRole::pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                        ])
                        ->columns(2)
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                        ->columnSpanFull(),
                    Textarea::make('remarks')
                        ->rows(3)
                        ->placeholder('Add notes or additional information if any')
                        ->columnSpanFull(),
                ]),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }
}
