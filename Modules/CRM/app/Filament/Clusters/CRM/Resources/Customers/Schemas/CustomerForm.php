<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Customers\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Customer;
use Modules\MasterData\Enums\ActiveStatus;
use Modules\MasterData\Enums\Gender;
use Modules\MasterData\Enums\LegalEntityType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles\Schemas\ContactRoleForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Models\ContactRole;
use Modules\MasterData\Models\District;
use Modules\MasterData\Models\Province;
use Modules\MasterData\Models\Regency;
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
            Section::make(__('Customer Profile'))
                ->description(__('Core information for the customer, including legal entity type, identifying code, and contact details.'))
                ->schema([
                    TextInput::make('code')
                        ->label(__('Customer Code'))
                        ->unique(Customer::class, 'code', ignoreRecord: true)
                        ->maxLength(3)
                        ->placeholder(__('e.g. GIA'))
                        ->helperText(__('Unique 3-character customer abbreviation used in project codes.'))
                        ->disabled(fn (?Customer $record) => $record !== null)
                        ->hidden(fn (?Customer $record) => $record === null && $isCreateOption)
                        ->dehydrated(),
                    Select::make('legal_entity_type')
                        ->label(__('Legal Entity Type'))
                        ->options(LegalEntityType::class)
                        ->required()
                        ->searchable()
                        ->placeholder(__('Select legal entity'))
                        ->native(false)
                        ->helperText(__('The official legal status of the organization (e.g., PT, CV).')),
                    TextInput::make('name')
                        ->label(__('Customer Name'))
                        ->required()
                        ->maxLength(255)
                        ->placeholder(__('e.g. Garuda Indonesia'))
                        ->helperText(__('The official registered name of the customer.')),
                    TextInput::make('email')
                        ->label(__('Corporate Email'))
                        ->email()
                        ->maxLength(255)
                        ->placeholder(__('example@email.com'))
                        ->helperText(__('Main contact email for the company.')),
                    TextInput::make('phone')
                        ->label(__('Corporate Phone'))
                        ->tel()
                        ->maxLength(255)
                        ->placeholder(__('081234567890'))
                        ->helperText(__('Main contact phone number for the company.')),
                    Select::make('status')
                        ->label(__('Status'))
                        ->options(ActiveStatus::class)
                        ->required()
                        ->default(ActiveStatus::Active)
                        ->helperText(__('Enable or disable this customer in the system.')),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make(__('Address Details'))
                ->description(__('Specify the official administrative and mailing location for this customer.'))
                ->icon('heroicon-o-map-pin')
                ->schema([
                    TextInput::make('address')
                        ->label(__('Street Address'))
                        ->maxLength(500)
                        ->placeholder(__('e.g. Soekarno-Hatta International Airport Terminal 3'))
                        ->helperText(__('Complete physical address of the company.'))
                        ->columnSpanFull(),
                    Select::make('province_id')
                        ->label(__('Province'))
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
                        ->placeholder(__('Select province'))
                        ->helperText(__('Geographic province location.')),
                    Select::make('regency_id')
                        ->label(__('Regency / City'))
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
                        ->placeholder(__('Select regency'))
                        ->helperText(__('Geographic city or regency location.'))
                        ->visible(fn (Get $get) => filled($get('province_id'))),
                    Select::make('district_id')
                        ->label(__('District'))
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
                        ->placeholder(__('Select district'))
                        ->helperText(__('Geographic district location.'))
                        ->visible(fn (Get $get) => filled($get('regency_id'))),
                    Select::make('village_id')
                        ->label(__('Village / Ward'))
                        ->relationship('village', 'name', fn ($query, Get $get) => $query->where('district_id', $get('district_id')))
                        ->searchable()
                        ->preload()
                        ->placeholder(__('Select village'))
                        ->helperText(__('Geographic village or ward location.'))
                        ->visible(fn (Get $get) => filled($get('district_id'))),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make(__('Customer Contacts'))
                ->description(__('List of key individual contacts at the customer organization.'))
                ->schema([
                    Repeater::make('contacts')
                        ->label(__(''))
                        ->schema([
                            Select::make('gender')
                                ->label(__('Salutation'))
                                ->options(Gender::class)
                                ->required()
                                ->native(false)
                                ->placeholder(__('Select gender')),
                            TextInput::make('name')
                                ->label(__('Full Name'))
                                ->required()
                                ->placeholder(__('e.g. John Doe')),
                            TextInput::make('email')
                                ->label(__('Email Address'))
                                ->email()
                                ->placeholder(__('john.doe@example.com')),
                            TextInput::make('phone')
                                ->label(__('Phone Number'))
                                ->tel()
                                ->placeholder(__('+62 812 3456 7890')),
                            TextInput::make('job_position')
                                ->label(__('Job Title'))
                                ->placeholder(__('e.g. Procurement Manager')),
                            Select::make('type')
                                ->label(__('Functional Role'))
                                ->options(ContactRole::pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->placeholder(__('Select role'))
                                ->helperText(__('The primary function of this contact person.'))
                                ->createOptionForm(ContactRoleForm::schema())
                                ->createOptionAction(fn (Action $action) => $action->slideOver())
                                ->createOptionUsing(fn (array $data) => ContactRole::create($data)->id),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->addActionLabel('Add New Contact Person'),
                ]),

            Section::make(__('Project Areas'))
                ->description(__('Assign existing branches or project locations to this customer, or create new ones.'))
                ->schema([
                    Select::make('projectAreas')
                        ->relationship('projectAreas', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->createOptionForm(ProjectAreaForm::schema(false))
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->helperText(__('Select the project areas associated with this customer.')),
                ]),

        ];
    }
}
