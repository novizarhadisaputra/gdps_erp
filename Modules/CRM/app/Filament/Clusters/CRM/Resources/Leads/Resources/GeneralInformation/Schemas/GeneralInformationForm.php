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
use Modules\CRM\Models\GeneralInformation;

class GeneralInformationForm
{
    public static function schema(): array
    {
        return [
            Section::make('Basic Information')
                ->schema([
                    TextInput::make('document_number')
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
                        ->default('draft'),
                    Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->default(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords ? $livewire->getOwnerRecord()->customer_id : null),
                    TextInput::make('scope_of_work')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->default(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords ? $livewire->getOwnerRecord()->title : null),
                    Textarea::make('description')
                        ->rows(3)
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
                                ->default(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords ? $livewire->getOwnerRecord()->start_date : null),
                            DatePicker::make('estimated_end_date')
                                ->required()
                                ->native(false)
                                ->default(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords ? $livewire->getOwnerRecord()->end_date : null),
                            Select::make('project_area_id')
                                ->relationship('projectArea', 'name')
                                ->label('Project Area')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->default(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords ? $livewire->getOwnerRecord()->project_area_id : null),
                            TextInput::make('location')
                                ->placeholder('Project site or specific location'),
                            Select::make('sales_plan_id')
                                ->relationship('salesPlan', 'project_code')
                                ->getOptionLabelFromRecordUsing(fn ($record) => $record->project_code ?? 'No Project Code')
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
                        ->placeholder('Deskripsi kualifikasi tenaga kerja yang dibutuhkan'),
                    Textarea::make('work_activities')
                        ->rows(3)
                        ->placeholder('Deskripsi aktivitas pekerjaan utama'),
                    Textarea::make('service_level')
                        ->rows(3)
                        ->placeholder('SLA atau tingkat layanan yang disepakati'),
                    Textarea::make('billing_requirements')
                        ->rows(3)
                        ->placeholder('Persyaratan penagihan (billing)'),
                    Repeater::make('risk_management')
                        ->label('Risk Management')
                        ->simple(TextInput::make('risk')->required())
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Documentation')
                ->description('Upload Term of Reference, RFP, and RFI documents.')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('tor')
                        ->collection('tor')
                        ->label('ToR Document')
                        ->disk(name: 's3')
                        ->visibility('private'),
                    SpatieMediaLibraryFileUpload::make('rfp')
                        ->collection('rfp')
                        ->label('RFP Document')
                        ->disk(name: 's3')
                        ->visibility('private'),
                    SpatieMediaLibraryFileUpload::make('rfi')
                        ->collection('rfi')
                        ->label('RFI Document')
                        ->disk(name: 's3')
                        ->visibility('private'),
                ])->columns(3),

            Section::make('PICs & Remarks')
                ->schema([
                    Repeater::make('pics')
                        ->relationship('pics')
                        ->schema([
                            TextInput::make('name')
                                ->required(),
                            TextInput::make('phone'),
                            TextInput::make('email')
                                ->email(),
                            Select::make('contact_role_id')
                                ->label('Role')
                                ->relationship('contactRole', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->columns(2)
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                        ->columnSpanFull(),
                    Textarea::make('remarks')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(GeneralInformation::class)
            ->components(static::schema());
    }
}
