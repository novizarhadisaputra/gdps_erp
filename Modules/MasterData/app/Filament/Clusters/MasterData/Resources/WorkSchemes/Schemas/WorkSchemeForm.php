<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\WorkScheme;

class WorkSchemeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(WorkScheme::class)
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Work Scheme Configuration')
                ->description('Define the scheduling and attendance schemes for different employee groups.')
                ->schema([
                    TextInput::make('name')
                        ->label('Scheme Name')
                        ->placeholder('e.g. 5/2 Office, 2-2-2 Shift, Remote')
                        ->helperText('The descriptive name of the work scheme.')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label('Scheme Code')
                        ->placeholder('e.g. WS-OFFICE, WS-SHIFT')
                        ->helperText('A unique short code identifying the work scheme.')
                        ->required()
                        ->unique(WorkScheme::class, 'code', ignoreRecord: true),
                ])->columns(2),

            Section::make('Status & Defaults')
                ->description('Manage the availability and default status of this work scheme.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Enable or disable this scheme for assignment.'),
                    Toggle::make('is_default')
                        ->label('Default Scheme')
                        ->default(false)
                        ->helperText('Set as the default scheme for new employee profiles.'),
                ])->columns(2),
        ];
    }
}
