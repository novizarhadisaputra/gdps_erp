<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles\Schemas\ContactRoleForm;
use Modules\MasterData\Models\ContactRole;
use Modules\MasterData\Models\Customer;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Customer Profile')
                ->schema([
                    TextInput::make('code')
                        ->label('Customer Code')
                        ->maxLength(10)
                        ->unique(Customer::class, 'code', ignoreRecord: true)
                        ->placeholder('Leave empty for auto-generate'),
                    Select::make('legal_entity_type')
                        ->label('Legal Entity Type')
                        ->options([
                            'PT' => 'PT (Limited Liability Company)',
                            'CV' => 'CV (Limited Partnership)',
                            'UD' => 'UD (Trading Business)',
                            'Firma' => 'Firma (General Partnership)',
                            'Koperasi' => 'Cooperative',
                            'Yayasan' => 'Foundation',
                            'Individual' => 'Individual',
                            'Other' => 'Other',
                        ])
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
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Telepon')
                        ->tel()
                        ->maxLength(20),
                    TextInput::make('address')
                        ->label('Alamat')
                        ->maxLength(500),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                        ])
                        ->required()
                        ->default('active'),
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
        ];
    }
}
