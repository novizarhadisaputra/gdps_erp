<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Enums\RiskLevel;

class JobPositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Job Position Information')
                ->description('Define the job roles available for project staffing, including their risk levels and labor intensity.')
                ->schema([
                    TextInput::make('name')
                        ->label('Position Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. Lead Cleaner, Security Officer')
                        ->helperText('The official title for this job position.'),
                    TextInput::make('code')
                        ->label('Position Code')
                        ->placeholder('Auto-generated')
                        ->readOnly()
                        ->hiddenOn('create')
                        ->helperText('The unique identifier for this role.'),
                    Select::make('risk_level')
                        ->label('Risk Level')
                        ->options(RiskLevel::class)
                        ->default(RiskLevel::VeryLow)
                        ->required()
                        ->placeholder('Select risk level')
                        ->helperText('The workplace safety risk associated with this role.'),
                    Toggle::make('is_labor_intensive')
                        ->label('Labor Intensive')
                        ->default(false)
                        ->helperText('Enable if this role primarily involves physical labor.'),
                ])->columns(2),

            Section::make('Status & Defaults')
                ->description('Manage the availability and default status of this job position.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Determines if this position can be assigned to manpower records.'),
                    Toggle::make('is_default')
                        ->label('Default Position')
                        ->default(false)
                        ->helperText('Set as the default choice for new manpower assignments.'),
                ])->columns(2),
        ];
    }
}
