<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Customers\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Customer;
use Modules\MasterData\Enums\ActiveStatus;
use Modules\MasterData\Enums\LegalEntityType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles\Schemas\ContactRoleForm;
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
            Section::make('Customer Profile')
                ->schema([
                    TextInput::make('code')
                        ->label('Customer Code')
                        ->unique(Customer::class, 'code', ignoreRecord: true)
                        ->maxLength(3)
                        ->placeholder('Auto-generated')
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
                        ->native(false),
                    TextInput::make('name')
                        ->label('Customer Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Example: Garuda Indonesia'),
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255)
                        ->placeholder('example@email.com'),
                    TextInput::make('phone')
                        ->label('Phone')
                        ->tel()
                        ->maxLength(255)
                        ->placeholder('081234567890'),
                    Select::make('status')
                        ->label('Status')
                        ->options(ActiveStatus::class)
                        ->required()
                        ->default(ActiveStatus::Active),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Address Details')
                ->description('Specify the official administrative location for this customer.')
                ->icon('heroicon-o-map-pin')
                ->schema([
                    TextInput::make('address')
                        ->label('Street Address')
                        ->maxLength(500)
                        ->placeholder('Example St. No. 123')
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
                        ->placeholder('Select province'),
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
                        ->visible(fn (Get $get) => filled($get('regency_id'))),
                    Select::make('village_id')
                        ->label('Village / Ward')
                        ->relationship('village', 'name', fn ($query, Get $get) => $query->where('district_id', $get('district_id')))
                        ->searchable()
                        ->preload()
                        ->placeholder('Select village')
                        ->visible(fn (Get $get) => filled($get('district_id'))),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Repeater::make('contacts')
                ->label('Customer Contacts')
                ->schema([
                    Select::make('gender')
                        ->label('Gender')
                        ->options(\Modules\MasterData\Enums\Gender::class)
                        ->required()
                        ->native(false),
                    TextInput::make('name')->required(),
                    TextInput::make('email')->email(),
                    TextInput::make('phone')->tel(),
                    TextInput::make('job_position')->label('Job Position'),
                    Select::make('type')
                        ->label('Functional Role')
                        ->options(ContactRole::pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm(ContactRoleForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->createOptionUsing(fn (array $data) => ContactRole::create($data)->id),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make('Legal & Company Documents')
                ->description('Official corporate and legal registration files.')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('npwp')
                        ->collection('npwp')
                        ->label('NPWP Document')
                        ->disk('s3')
                        ->visibility('private'),
                    SpatieMediaLibraryFileUpload::make('legal_documents')
                        ->collection('legal_documents')
                        ->label('Legal Documents')
                        ->disk('s3')
                        ->visibility('private')
                        ->multiple(),
                    SpatieMediaLibraryFileUpload::make('company_profile')
                        ->collection('company_profile')
                        ->label('Company Profile')
                        ->disk('s3')
                        ->visibility('private'),
                ])
                ->columns(3)
                ->columnSpanFull(),
        ];
    }
}
