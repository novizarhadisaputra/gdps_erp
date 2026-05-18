<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\AssetGroups\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Enums\AssetGroupType;
use Modules\MasterData\Models\AssetGroup;

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
            Section::make(__('Asset Group Details'))
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->unique(AssetGroup::class, 'name', ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder(__('Group I (Non-Building)')),
                    Select::make('type')
                        ->options(AssetGroupType::class)
                        ->required()
                        ->searchable(),
                    TextInput::make('useful_life_years')
                        ->label(__('Useful Life (Years)'))
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100),
                    TextInput::make('rate_straight_line')
                        ->label(__('Rate Straight Line (%)'))
                        ->numeric()
                        ->suffix('%')
                        ->maxValue(100)
                        ->placeholder(__('25.00'))
                        ->helperText(__('Tarif Garis Lurus')),
                    TextInput::make('rate_declining_balance')
                        ->label(__('Rate Double Declining (%)'))
                        ->numeric()
                        ->suffix('%')
                        ->maxValue(100)
                        ->placeholder(__('50.00'))
                        ->helperText(__('Tarif Saldo Menurun Ganda')),
                    Textarea::make('description')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->required(),
                    Toggle::make('is_default')
                        ->label(__('Default Record'))
                        ->helperText(__('Hanya satu record yang dapat menjadi default.'))
                        ->default(false),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }
}
