<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile Information')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),

                Section::make('Digital Signature')
                    ->description('Upload your signature image and set a PIN for document approval.')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('signature_image')
                            ->label('Signature Image')
                            ->collection('signature')
                            ->image()
                            ->imageEditor()
                            ->downloadable()
                            ->openable()
                            ->maxSize(2048),

                        TextInput::make('signature_pin')
                            ->label('Signature PIN')
                            ->password()
                            ->revealable()
                            ->numeric()
                            ->length(6)
                            ->helperText('A 6-digit PIN required for approving documents.')
                            ->dehydrateStateUsing(fn ($state) => $state ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn ($record) => $record->signature_pin === null),
                    ]),
            ]);
    }
}
