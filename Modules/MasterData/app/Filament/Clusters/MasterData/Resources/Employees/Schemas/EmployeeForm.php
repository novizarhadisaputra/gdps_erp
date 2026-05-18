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
            Section::make(__('General Information'))
                ->description(__('Provide the basic employment details for this staff member.'))
                ->schema([
                    TextInput::make('code')
                        ->label(__('Employee ID'))
                        ->placeholder(__('Auto-generated'))
                        ->readOnly()
                        ->unique(Employee::class, 'code', ignoreRecord: true)
                        ->helperText(__('Unique identification code for the employee.')),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder(__('Full Name'))
                        ->helperText(__('Enter the full legal name of the employee.')),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(Employee::class, 'email', ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder(__('employee@company.com'))
                        ->helperText(__('Work email address for internal communications.')),
                    TextInput::make('position')
                        ->maxLength(255)
                        ->placeholder(__('e.g. Account Manager'))
                        ->helperText(__('The current job title assigned to this employee.')),
                    TextInput::make('department')
                        ->maxLength(255)
                        ->placeholder(__('e.g. Human Resources'))
                        ->helperText(__('The organizational unit where the employee works.')),
                    Select::make('status')
                        ->label(__('Active Status'))
                        ->options(ActiveStatus::class)
                        ->required()
                        ->default(ActiveStatus::Active)
                        ->helperText(__('Controls whether the employee is currently active in the system.')),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make(__('Employment Documents'))
                ->description(__('Upload and manage official employee records and certifications.'))
                ->schema([
                    SpatieMediaLibraryFileUpload::make('identity_card')
                        ->collection('identity_card')
                        ->label(__('Identity Card'))
                        ->placeholder(__('Click or drag to upload scan'))
                        ->helperText(__('Scan of KTP or equivalent identity document.'))
                        ->visibility('private'),
                    SpatieMediaLibraryFileUpload::make('resume')
                        ->collection('resume')
                        ->label(__('Resume'))
                        ->placeholder(__('Upload latest CV'))
                        ->helperText(__('Most recent professional curriculum vitae.'))
                        ->visibility('private'),
                    SpatieMediaLibraryFileUpload::make('employment_contract')
                        ->collection('employment_contract')
                        ->label(__('Employment Contract'))
                        ->placeholder(__('Upload signed contract'))
                        ->helperText(__('Final signed copy of the employment agreement.'))
                        ->visibility('private'),
                ])
                ->columns(3)
                ->columnSpanFull(),
        ];
    }
}
