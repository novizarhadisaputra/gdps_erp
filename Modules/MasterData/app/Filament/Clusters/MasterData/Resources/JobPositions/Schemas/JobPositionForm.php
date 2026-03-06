<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\MasterData\Enums\RiskLevel;
use Modules\MasterData\Models\FixedAllowance;
use Modules\MasterData\Models\NonFixedAllowance;

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

            Section::make('Default Allowances (Remuneration Blueprint)')
                ->description('Tentukan komponen tunjangan default untuk jabatan ini.')
                ->schema([
                    Repeater::make('fixedAllowances')
                        ->label('Tunjangan Tetap')
                        ->relationship('fixedAllowances')
                        ->schema([
                            Select::make('fixed_allowance_id')
                                ->label('Komponen')
                                ->options(FixedAllowance::query()->where('is_active', true)->pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->columnSpan(3),
                            TextInput::make('amount')
                                ->label('Nominal (Rp)')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('IDR ')
                                ->default(0)
                                ->required()
                                ->columnSpan(2),
                        ])
                        ->columns(5)
                        ->columnSpanFull()
                        ->itemLabel(fn (array $state): ?string => FixedAllowance::find($state['fixed_allowance_id'] ?? null)?->name ?? 'Tunjangan Tetap'),

                    Repeater::make('nonFixedAllowances')
                        ->label('Tunjangan Tidak Tetap')
                        ->relationship('nonFixedAllowances')
                        ->schema([
                            Select::make('non_fixed_allowance_id')
                                ->label('Komponen')
                                ->options(NonFixedAllowance::query()->where('is_active', true)->pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->columnSpan(3),
                            TextInput::make('amount')
                                ->label('Nominal (Rp)')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('IDR ')
                                ->default(0)
                                ->required()
                                ->columnSpan(2),
                        ])
                        ->columns(5)
                        ->columnSpanFull()
                        ->itemLabel(fn (array $state): ?string => NonFixedAllowance::find($state['non_fixed_allowance_id'] ?? null)?->name ?? 'Tunjangan Tidak Tetap'),
                ]),
        ];
    }
}
