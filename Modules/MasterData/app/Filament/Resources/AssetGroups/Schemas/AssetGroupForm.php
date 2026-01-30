<?php

namespace Modules\MasterData\Filament\Resources\AssetGroups\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Modules\MasterData\Enums\AssetGroupType;

class AssetGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->placeholder('Group I (Non-Building)'),
            Select::make('type')
                ->options(AssetGroupType::class)
                ->required(),
            TextInput::make('useful_life_years')
                ->label('Useful Life (Years)')
                ->required()
                ->numeric()
                ->minValue(0)
                ->maxValue(100),
            TextInput::make('rate_straight_line')
                ->label('Rate Straight Line (%)')
                ->numeric()
                ->suffix('%')
                ->maxValue(100)
                ->placeholder('25.00')
                ->helperText('Tarif Garis Lurus'),
            TextInput::make('rate_declining_balance')
                ->label('Rate Double Declining (%)')
                ->numeric()
                ->suffix('%')
                ->maxValue(100)
                ->placeholder('50.00')
                ->helperText('Tarif Saldo Menurun Ganda'),
            Textarea::make('description')
                ->maxLength(65535)
                ->columnSpanFull(),
        ];
    }
}
