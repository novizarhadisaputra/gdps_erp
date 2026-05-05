<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Finance\Models\ChartOfAccount;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Header Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('number')
                                    ->label('Voucher Number')
                                    ->placeholder('Auto-generated')
                                    ->disabled()
                                    ->dehydrated(false),

                                DatePicker::make('date')
                                    ->label('Transaction Date')
                                    ->required()
                                    ->default(now()),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Journal Items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        Select::make('chart_of_account_id')
                                            ->label('Account')
                                            ->options(ChartOfAccount::query()->get()->mapWithKeys(fn ($item) => [$item->id => "{$item->code} - {$item->name}"]))
                                            ->searchable()
                                            ->required()
                                            ->columnSpan(2),

                                        TextInput::make('debit')
                                            ->numeric()
                                            ->default(0)
                                            ->required(),

                                        TextInput::make('credit')
                                            ->numeric()
                                            ->default(0)
                                            ->required(),

                                        TextInput::make('note')
                                            ->label('Item Note')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columns(1)
                            ->minItems(2)
                            ->itemLabel(fn (array $state): ?string => ($state['debit'] > 0 ? 'Debit: '.number_format($state['debit'], 2) : ($state['credit'] > 0 ? 'Credit: '.number_format($state['credit'], 2) : 'New Item'))),
                    ]),
            ]);
    }
}
