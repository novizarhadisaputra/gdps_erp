<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Enums\ActiveStatus;
use Modules\MasterData\Enums\LegalEntityType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles\Schemas\ContactRoleForm;
use Modules\MasterData\Models\ContactRole;
use Modules\MasterData\Models\Customer;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(Customer::class)
            ->components(static::schema());
    }

    public static function schema(bool $isCreateOption = false): array
    {
        return [
            Section::make('Customer Profile')
                ->schema([
                    TextInput::make('code')
                        ->label('Customer Code')
                        ->maxLength(10)
                        ->unique(Customer::class, 'code', ignoreRecord: true)
                        ->placeholder('Leave empty for auto-generate')
                        ->hidden($isCreateOption)
                        ->disabled(fn ($record) => $record !== null)
                        ->dehydrated(),
                    Select::make('legal_entity_type')
                        ->label('Legal Entity Type')
                        ->options(LegalEntityType::class)
                        ->searchable()
                        ->placeholder('Select legal entity type'),
                    TextInput::make('name')
                        ->label('Customer Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Garuda Indonesia'),
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Telepon')
                        ->tel()
                        ->required()
                        ->maxLength(20),
                    TextInput::make('address')
                        ->label('Alamat')
                        ->maxLength(500),
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

                        ->visibility('private'),
                    SpatieMediaLibraryFileUpload::make('legal_documents')
                        ->collection('legal_documents')
                        ->label('Legal Documents')

                        ->visibility('private')
                        ->multiple(),
                    SpatieMediaLibraryFileUpload::make('company_profile')
                        ->collection('company_profile')
                        ->label('Company Profile')

                        ->visibility('private'),
                ])
                ->columns(3)
                ->columnSpanFull(),
        ];
    }
}
