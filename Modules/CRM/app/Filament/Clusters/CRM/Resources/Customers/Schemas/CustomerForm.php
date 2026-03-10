<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Customers\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Customer;
use Modules\MasterData\Enums\ActiveStatus;
use Modules\MasterData\Enums\LegalEntityType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles\Schemas\ContactRoleForm;
use Modules\MasterData\Models\ContactRole;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(bool $isCreateOption = false): array
    {
        return [
            Section::make('Customer Profile')
                ->schema([
                    TextInput::make('code')
                        ->label('Customer Code')
                        ->required()
                        ->unique(Customer::class, 'code', ignoreRecord: true)
                        ->maxLength(3)
                        ->placeholder('3 characters (e.g. ACS)')
                        ->helperText('Unique 3-character customer abbreviation used in project codes.')
                        ->disabled(fn ($record) => $record !== null)
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
                        ->required()
                        ->maxLength(255)
                        ->placeholder('example@email.com'),
                    TextInput::make('phone')
                        ->label('Phone')
                        ->tel()
                        ->required()
                        ->maxLength(255)
                        ->placeholder('081234567890'),
                    TextInput::make('address')
                        ->label('Address')
                        ->maxLength(500)
                        ->placeholder('Example St. No. 123'),
                    Select::make('status')
                        ->label('Status')
                        ->options(ActiveStatus::class)
                        ->required()
                        ->default(ActiveStatus::Active),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Repeater::make('contacts')
                ->label('Customer Contacts')
                ->schema([
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
