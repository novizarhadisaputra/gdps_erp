<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Enums\ActiveStatus;
use Modules\MasterData\Models\Employee;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make()
                ->schema([
                    TextInput::make('code')
                        ->required()
                        ->unique(Employee::class, 'code', ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('EMP001'),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('John Doe'),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(Employee::class, 'email', ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('john.doe@example.com'),
                    TextInput::make('position')
                        ->maxLength(255)
                        ->placeholder('Senior Developer'),
                    TextInput::make('department')
                        ->maxLength(255)
                        ->placeholder('IT Department'),
                    Select::make('status')
                        ->options(ActiveStatus::class)
                        ->required()
                        ->default(ActiveStatus::Active),
                ])
                ->columns(2)
                ->columnSpanFull(),
            SpatieMediaLibraryFileUpload::make('identity_card')
                ->collection('identity_card')
                ->label('Identity Card')
                ->disk('s3')
                ->visibility('private'),
            SpatieMediaLibraryFileUpload::make('resume')
                ->collection('resume')
                ->label('Resume')
                ->disk('s3')
                ->visibility('private'),
            SpatieMediaLibraryFileUpload::make('employment_contract')
                ->collection('employment_contract')
                ->label('Employment Contract')
                ->disk('s3')
                ->visibility('private'),
        ];
    }
}
