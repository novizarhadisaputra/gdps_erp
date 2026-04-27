<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\Finance\Models\Invoice;
use Modules\Project\Models\Project;

class AccrueRevenueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('General Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('project_id')
                                    ->label('Project')
                                    ->options(Project::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->live(),
                                Select::make('month')
                                    ->label('Month')
                                    ->options([
                                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                                    ])
                                    ->required()
                                    ->default(now()->month),
                                TextInput::make('year')
                                    ->label('Year')
                                    ->numeric()
                                    ->default(now()->year)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Revenue & Cost Details')
                    ->schema([
                        Select::make('invoice_id')
                            ->label('Related Invoice (Optional)')
                            ->options(fn (Get $get) => Invoice::where('customer_id', Project::find($get('project_id'))?->customer_id)->pluck('number', 'id'))
                            ->searchable()
                            ->placeholder('Select invoice to auto-fill cost')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                if ($state) {
                                    $invoice = Invoice::find($state);
                                    if ($invoice) {
                                        $set('amount_cost', $invoice->total_amount);
                                    }
                                } else {
                                    $set('amount_cost', $get('amount_revenue'));
                                }
                            }),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('amount_revenue')
                                    ->label('Accrue Revenue Amount')
                                    ->prefix('IDR')
                                    ->numeric()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        if (! $get('invoice_id')) {
                                            $set('amount_cost', $state);
                                        }
                                    }),
                                TextInput::make('amount_cost')
                                    ->label('Amount Cost (Actual/Settled)')
                                    ->prefix('IDR')
                                    ->numeric()
                                    ->required()
                                    ->helperText('Will be auto-filled from Invoice if selected, otherwise matches Revenue.'),
                            ]),
                        Textarea::make('description')
                            ->label('Notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
