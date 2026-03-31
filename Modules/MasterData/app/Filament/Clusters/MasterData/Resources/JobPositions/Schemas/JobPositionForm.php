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
            Section::make('General Information')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Select::make('risk_level')
                        ->options(RiskLevel::class)
                        ->default(RiskLevel::VeryLow)
                        ->required(),
                    Toggle::make('is_labor_intensive')
                        ->label('Labor Intensive')
                        ->default(false),
                ])->columns(3),
        ];
    }
}
