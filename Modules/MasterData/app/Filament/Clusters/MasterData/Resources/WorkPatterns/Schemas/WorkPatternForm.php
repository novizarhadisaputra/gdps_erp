<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkPatterns\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\WorkPattern;

class WorkPatternForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Work Pattern Details')
                ->description('Define the weekly and daily work configuration.')
                ->schema([
                    TextInput::make('code')
                        ->label('Code')
                        ->placeholder('e.g. 5-8, 6-7')
                        ->helperText('Unique code for this pattern.')
                        ->required()
                        ->unique(WorkPattern::class, 'code', ignoreRecord: true),
                    TextInput::make('name')
                        ->label('Name')
                        ->placeholder('e.g. Standard 5 Days, Shift Pattern')
                        ->helperText('Descriptive name of the work pattern.')
                        ->required(),
                    TextInput::make('days_per_week')
                        ->label('Days Per Week')
                        ->numeric()
                        ->default(5)
                        ->required()
                        ->helperText('Number of working days in a week.'),
                    TextInput::make('hours_per_day')
                        ->label('Hours Per Day')
                        ->numeric()
                        ->default(8)
                        ->required()
                        ->helperText('Standard working hours in a day.'),
                    Toggle::make('is_shift')
                        ->label('Is Shift Pattern')
                        ->default(false)
                        ->helperText('Whether this pattern involves shifts.'),
                    TextInput::make('description')
                        ->label('Description')
                        ->placeholder('Enter additional details...')
                        ->helperText('Optional details about this pattern.'),
                ])->columns(2),

            Section::make('Status & Defaults')
                ->description('Manage active status and default settings.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Determines if this pattern is currently active.'),
                    Toggle::make('is_default')
                        ->label('Set as Default')
                        ->default(false)
                        ->helperText('If enabled, this will be the default pattern for new configurations.'),
                ])->columns(2),
        ];
    }
}
