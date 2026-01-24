<?php

namespace Modules\MasterData\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Client Code')
                    ->maxLength(10)
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
                    ->label('Company Name')
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
            ]);
    }
}
