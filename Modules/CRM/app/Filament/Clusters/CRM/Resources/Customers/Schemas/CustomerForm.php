<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Customers\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Customer;
use Modules\MasterData\Enums\ActiveStatus;
use Modules\MasterData\Enums\LegalEntityType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles\Schemas\ContactRoleForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes\Schemas\RevenueTypeForm;
use Modules\MasterData\Models\ContactRole;
use Modules\MasterData\Models\District;
use Modules\MasterData\Models\Province;
use Modules\MasterData\Models\Regency;
use Modules\MasterData\Models\RevenueType;
use Modules\MasterData\Services\WilayahSyncService;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(bool $isCreateOption = false): array
    {
        if (Province::count() === 0) {
            app(WilayahSyncService::class)->syncProvinces();
        }

        return [
            Section::make('Customer Profile')
                ->description('Core information for the customer, including legal entity type, identifying code, and contact details.')
                ->schema([
                    TextInput::make('code')
                        ->label('Customer Code')
                        ->unique(Customer::class, 'code', ignoreRecord: true)
                        ->maxLength(3)
                        ->placeholder('e.g. GIA')
                        ->helperText('Unique 3-character customer abbreviation used in project codes.')
                        ->disabled(fn ($record) => $record !== null)
                        ->hidden(fn ($record) => $record === null && $isCreateOption)
                        ->dehydrated(),
                    Select::make('legal_entity_type')
                        ->label('Legal Entity Type')
                        ->options(LegalEntityType::class)
                        ->required()
                        ->searchable()
                        ->placeholder('Select legal entity')
                        ->native(false)
                        ->helperText('The official legal status of the organization (e.g., PT, CV).'),
                    TextInput::make('name')
                        ->label('Customer Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. Garuda Indonesia')
                        ->helperText('The official registered name of the customer.'),
                    TextInput::make('email')
                        ->label('Corporate Email')
                        ->email()
                        ->maxLength(255)
                        ->placeholder('example@email.com')
                        ->helperText('Main contact email for the company.'),
                    TextInput::make('phone')
                        ->label('Corporate Phone')
                        ->tel()
                        ->maxLength(255)
                        ->placeholder('081234567890')
                        ->helperText('Main contact phone number for the company.'),
                    Select::make('status')
                        ->label('Status')
                        ->options(ActiveStatus::class)
                        ->required()
                        ->default(ActiveStatus::Active)
                        ->helperText('Enable or disable this customer in the system.'),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Address Details')
                ->description('Specify the official administrative and mailing location for this customer.')
                ->icon('heroicon-o-map-pin')
                ->schema([
                    TextInput::make('address')
                        ->label('Street Address')
                        ->maxLength(500)
                        ->placeholder('e.g. Soekarno-Hatta International Airport Terminal 3')
                        ->helperText('Complete physical address of the company.')
                        ->columnSpanFull(),
                    Select::make('province_id')
                        ->label('Province')
                        ->relationship('province', 'name')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('regency_id', null);
                            $set('district_id', null);
                            $set('village_id', null);
                            if ($state) {
                                $province = Province::find($state);
                                if ($province && $province->regencies()->count() === 0) {
                                    app(WilayahSyncService::class)->syncRegencies($province);
                                }
                            }
                        })
                        ->placeholder('Select province')
                        ->helperText('Geographic province location.'),
                    Select::make('regency_id')
                        ->label('Regency / City')
                        ->relationship('regency', 'name', fn ($query, Get $get) => $query->where('province_id', $get('province_id')))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('district_id', null);
                            $set('village_id', null);
                            if ($state) {
                                $regency = Regency::find($state);
                                if ($regency && $regency->districts()->count() === 0) {
                                    app(WilayahSyncService::class)->syncDistricts($regency);
                                }
                            }
                        })
                        ->placeholder('Select regency')
                        ->helperText('Geographic city or regency location.')
                        ->visible(fn (Get $get) => filled($get('province_id'))),
                    Select::make('district_id')
                        ->label('District')
                        ->relationship('district', 'name', fn ($query, Get $get) => $query->where('regency_id', $get('regency_id')))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('village_id', null);
                            if ($state) {
                                $district = District::find($state);
                                if ($district && $district->villages()->count() === 0) {
                                    app(WilayahSyncService::class)->syncVillages($district);
                                }
                            }
                        })
                        ->placeholder('Select district')
                        ->helperText('Geographic district location.')
                        ->visible(fn (Get $get) => filled($get('regency_id'))),
                    Select::make('village_id')
                        ->label('Village / Ward')
                        ->relationship('village', 'name', fn ($query, Get $get) => $query->where('district_id', $get('district_id')))
                        ->searchable()
                        ->preload()
                        ->placeholder('Select village')
                        ->helperText('Geographic village or ward location.')
                        ->visible(fn (Get $get) => filled($get('district_id'))),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('Customer Contacts')
                ->description('List of key individual contacts at the customer organization.')
                ->schema([
                    Repeater::make('contacts')
                        ->label('')
                        ->schema([
                            Select::make('gender')
                                ->label('Salutation')
                                ->options(\Modules\MasterData\Enums\Gender::class)
                                ->required()
                                ->native(false)
                                ->placeholder('Select gender'),
                            TextInput::make('name')
                                ->label('Full Name')
                                ->required()
                                ->placeholder('e.g. John Doe'),
                            TextInput::make('email')
                                ->label('Email Address')
                                ->email()
                                ->placeholder('john.doe@example.com'),
                            TextInput::make('phone')
                                ->label('Phone Number')
                                ->tel()
                                ->placeholder('+62 812 3456 7890'),
                            TextInput::make('job_position')
                                ->label('Job Title')
                                ->placeholder('e.g. Procurement Manager'),
                            Select::make('type')
                                ->label('Functional Role')
                                ->options(ContactRole::pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->placeholder('Select role')
                                ->helperText('The primary function of this contact person.')
                                ->createOptionForm(ContactRoleForm::schema())
                                ->createOptionAction(fn (Action $action) => $action->slideOver())
                                ->createOptionUsing(fn (array $data) => ContactRole::create($data)->id),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->addActionLabel('Add New Contact Person'),
                ]),

            Section::make('GL Account Mapping')
                ->description('Map specific SAP General Ledger accounts for this customer.')
                ->schema([
                    Repeater::make('accountMappings')
                        ->relationship('accountMappings')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    Select::make('type')
                                        ->label('Mapping Type')
                                        ->options([
                                            'accrual' => 'Accrual Revenue',
                                            'revenue' => 'Realized Revenue',
                                            'receivable' => 'Account Receivable',
                                            'expense' => 'Accrued Expense',
                                        ])
                                        ->required()
                                        ->native(false),
                                    Select::make('revenue_type_id')
                                        ->label('Revenue Type')
                                        ->relationship('revenueType', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('All Types')
                                        ->createOptionForm(RevenueTypeForm::schema())
                                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                                        ->createOptionUsing(fn (array $data) => RevenueType::create($data)->id),
                                    Select::make('revenue_segment_id')
                                        ->label('Revenue Segment')
                                        ->relationship('revenueSegment', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('All Segments'),
                                ]),
                            Select::make('chart_of_account_id')
                                ->label('GL Account')
                                ->relationship('chartOfAccount', 'name')
                                ->getOptionLabelFromRecordUsing(fn ($record) => "[{$record->code}] {$record->name}")
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->columns(1)
                        ->addActionLabel('Add New GL Mapping'),
                ])->columnSpanFull(),
        ];
    }
}
