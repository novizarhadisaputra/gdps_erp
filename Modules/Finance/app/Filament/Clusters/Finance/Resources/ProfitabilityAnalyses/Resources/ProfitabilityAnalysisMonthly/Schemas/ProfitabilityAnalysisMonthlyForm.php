<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProfitabilityAnalysisMonthlyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Identitas Periode')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('month')
                                ->options([
                                    'January' => 'January', 'February' => 'February', 'March' => 'March',
                                    'April' => 'April', 'May' => 'May', 'June' => 'June',
                                    'July' => 'July', 'August' => 'August', 'September' => 'September',
                                    'October' => 'October', 'November' => 'November', 'December' => 'December',
                                ])
                                ->required(),
                            TextInput::make('year')
                                ->numeric()
                                ->default(now()->year)
                                ->required(),
                            TextInput::make('status')
                                ->visible(fn (string $operation): bool => $operation !== 'create')
                                ->readOnly(),
                        ]),
                ])->columnSpanFull(),

            Section::make('Monthly Performance Summary')
                ->description('Financial performance snapshot focusing on Latest RoFo and Actual Revenue (AR).')
                ->visible(fn (string $operation): bool => $operation !== 'create')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('forecast_revenue')
                                ->label('Latest RoFo')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('IDR ')
                                ->visible(fn (string $operation): bool => $operation !== 'create')
                                ->helperText('Rolling forecast updated weekly.'),

                            TextInput::make('actual_revenue')
                                ->label('Actual Revenue (AR)')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('IDR ')
                                ->visible(fn (string $operation): bool => $operation !== 'create')
                                ->helperText('Realized revenue (AR) for this month.'),
                        ]),
                ])->columnSpanFull(),

            Section::make('Update History')
                ->description('Timeline of RoFo and Actual revenue (AR) adjustments.')
                ->visible(fn (string $operation): bool => $operation !== 'create')
                ->collapsible()
                ->schema([
                    Repeater::make('logs')
                        ->relationship('logs')
                        ->label(false)
                        ->itemLabel(fn (array $state): ?string => ($state['field_name'] === 'forecast_revenue' ? 'RoFo' : 'Actual').
                            ' adjustment recorded'
                        )
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('created_at')
                                        ->label('Timestamp')
                                        ->formatStateUsing(fn ($state) => \Illuminate\Support\Carbon::parse($state)->timezone('Asia/Jakarta')->format('d M Y, H:i'))
                                        ->readOnly(),
                                    TextInput::make('field_name')
                                        ->label('Component')
                                        ->formatStateUsing(fn ($state) => $state === 'forecast_revenue' ? 'RoFo' : 'Actual')
                                        ->readOnly(),
                                    TextInput::make('user_name')
                                        ->label('Updated By')
                                        ->formatStateUsing(fn ($record) => $record?->user?->name ?? 'System')
                                        ->readOnly(),
                                ]),
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('old_value')
                                        ->label('Previous Value')
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('IDR ')
                                        ->readOnly(),
                                    TextInput::make('new_value')
                                        ->label('New Value')
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('IDR ')
                                        ->readOnly(),
                                    TextInput::make('delta')
                                        ->label('Adjustment Value')
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('IDR ')
                                        ->readOnly(),
                                ]),
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false),
                ])->columnSpanFull(),
        ]);
    }
}
