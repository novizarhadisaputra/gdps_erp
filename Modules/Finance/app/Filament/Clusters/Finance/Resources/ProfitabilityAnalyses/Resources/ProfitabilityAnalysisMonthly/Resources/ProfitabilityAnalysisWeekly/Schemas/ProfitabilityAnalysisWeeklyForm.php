<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Resources\ProfitabilityAnalysisWeekly\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ProfitabilityAnalysisWeeklyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Weekly Performance Update')
                ->description('Report weekly earnings and update the end-of-month outlook.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('achieved_revenue')
                                ->label('Weekly Achievement (Real)')
                                ->numeric()
                                ->prefix('IDR ')
                                ->required()
                                ->helperText('Input the actual revenue earned THIS WEEK.'),

                            TextInput::make('projected_revenue')
                                ->label('Monthly Outlook (Forecast)')
                                ->numeric()
                                ->prefix('IDR ')
                                ->required()
                                ->helperText('Update the total expected revenue by end of month.'),
                        ]),
                    
                    Textarea::make('notes')
                        ->label('Progress Notes')
                        ->placeholder('Share highlights, challenges, or reasons for changes...')
                        ->required()
                        ->columnSpanFull(),
                ]),
            
            Section::make('Tracking Info')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('week_number')
                                ->label('Week Number')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(5)
                                ->required(),
                            TextInput::make('month')
                                ->readOnly(),
                            TextInput::make('year')
                                ->numeric()
                                ->readOnly(),
                        ]),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }
}
