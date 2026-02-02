<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityThresholds\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Finance\Models\ProfitabilityThreshold;

class ProfitabilityThresholdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Threshold Details')
                ->schema([
                    TextInput::make('name')
                        ->label('Threshold Name')
                        ->required()
                        ->helperText('e.g. Standard Project, Strategic Project')
                        ->unique(ProfitabilityThreshold::class, 'name', ignoreRecord: true)
                        ->maxLength(255),
                    TextInput::make('min_gpm')
                        ->label('Min. Gross Profit Margin')
                        ->numeric()
                        ->suffix('%')
                        ->required()
                        ->maxValue(100),
                    TextInput::make('min_npm')
                        ->label('Min. Net Profit Margin')
                        ->numeric()
                        ->suffix('%')
                        ->required()
                        ->maxValue(100),
                    Textarea::make('description')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }
}
