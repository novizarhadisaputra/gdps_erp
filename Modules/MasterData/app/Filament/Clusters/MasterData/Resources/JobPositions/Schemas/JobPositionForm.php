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
            Section::make(__('Job Position Information'))
                ->description(__('Define the job roles available for project staffing, including their risk levels and labor intensity.'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Position Name'))
                        ->required()
                        ->maxLength(255)
                        ->placeholder(__('e.g. Lead Cleaner, Security Officer'))
                        ->helperText(__('The official title for this job position.')),
                    TextInput::make('code')
                        ->label(__('Position Code'))
                        ->placeholder(__('Auto-generated'))
                        ->readOnly()
                        ->hiddenOn('create')
                        ->helperText(__('The unique identifier for this role.')),
                    Select::make('risk_level')
                        ->label(__('Risk Level'))
                        ->options(RiskLevel::class)
                        ->default(RiskLevel::VeryLow)
                        ->required()
                        ->placeholder(__('Select risk level'))
                        ->helperText(__('The workplace safety risk associated with this role.')),
                    Toggle::make('is_labor_intensive')
                        ->label(__('Labor Intensive'))
                        ->default(false)
                        ->helperText(__('Enable if this role primarily involves physical labor.')),
                ])->columns(2),

            Section::make(__('Status & Defaults'))
                ->description(__('Manage the availability and default status of this job position.'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Determines if this position can be assigned to manpower records.')),
                    Toggle::make('is_default')
                        ->label(__('Default Position'))
                        ->default(false)
                        ->helperText(__('Set as the default choice for new manpower assignments.')),
                ])->columns(2),
        ];
    }
}
