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
            Section::make('General Information')
                ->description('Provide the basic employment details for this staff member.')
                ->schema([
                    TextInput::make('code')
                        ->label('Employee ID')
                        ->placeholder('Auto-generated')
                        ->readOnly()
                        ->unique(Employee::class, 'code', ignoreRecord: true)
                        ->helperText('Unique identification code for the employee.'),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Full Name')
                        ->helperText('Enter the full legal name of the employee.'),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(Employee::class, 'email', ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('employee@company.com')
                        ->helperText('Work email address for internal communications.'),
                    TextInput::make('position')
                        ->maxLength(255)
                        ->placeholder('e.g. Account Manager')
                        ->helperText('The current job title assigned to this employee.'),
                    TextInput::make('department')
                        ->maxLength(255)
                        ->placeholder('e.g. Human Resources')
                        ->helperText('The organizational unit where the employee works.'),
                    Select::make('status')
                        ->label('Active Status')
                        ->options(ActiveStatus::class)
                        ->required()
                        ->default(ActiveStatus::Active)
                        ->helperText('Controls whether the employee is currently active in the system.'),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Employment Documents')
                ->description('Upload and manage official employee records and certifications.')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('identity_card')
                        ->collection('identity_card')
                        ->label('Identity Card')
                        ->placeholder('Click or drag to upload scan')
                        ->helperText('Scan of KTP or equivalent identity document.')
                        ->visibility('private'),
                    SpatieMediaLibraryFileUpload::make('resume')
                        ->collection('resume')
                        ->label('Resume')
                        ->placeholder('Upload latest CV')
                        ->helperText('Most recent professional curriculum vitae.')
                        ->visibility('private'),
                    SpatieMediaLibraryFileUpload::make('employment_contract')
                        ->collection('employment_contract')
                        ->label('Employment Contract')
                        ->placeholder('Upload signed contract')
                        ->helperText('Final signed copy of the employment agreement.')
                        ->visibility('private'),
                ])
                ->columns(3)
                ->columnSpanFull(),
        ];
    }
}
