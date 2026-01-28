<?php

namespace Modules\MasterData\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
            TextInput::make('code')
                ->label('Customer Code')
                ->maxLength(10)
                ->unique(ignoreRecord: true)
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
            Repeater::make('contacts')
                ->label('Customer Contacts')
                ->schema([
                    TextInput::make('name')->required(),
                    TextInput::make('email')->email(),
                    TextInput::make('phone')->tel(),
                    TextInput::make('job_position')->label('Job Position'),
                    Select::make('type')
                        ->options([
                            'Finance' => 'Finance',
                            'Procurement' => 'Procurement',
                            'Other' => 'Other',
                        ])
                        ->default('Other'),
                ])
                ->columns(2)
                ->collapsible(),
            Select::make('status')
                ->label('Status')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ])
                ->required()
                ->default('active'),
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
