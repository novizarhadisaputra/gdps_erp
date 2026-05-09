<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccrueRevenueInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Project Information')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('project.name')
                                ->label('Project'),
                            TextEntry::make('month')
                                ->formatStateUsing(fn (int $state): string => date('F', mktime(0, 0, 0, $state, 1))),
                            TextEntry::make('year'),
                        ]),
                ]),

            Section::make('Revenue Items')
                ->schema([
                    RepeatableEntry::make('items')
                        ->schema([
                            Grid::make(4)
                                ->schema([
                                    TextEntry::make('revenueType.name')
                                        ->label('Revenue Type')
                                        ->badge(),
                                    TextEntry::make('status')
                                        ->label('Status')
                                        ->state(fn ($record) => $record->is_reversed ? 'Reversed' : ($record->accrueInvoiceMappings()->exists() ? 'Partially Invoiced' : 'Pending'))
                                        ->badge()
                                        ->color(fn ($state) => match ($state) {
                                            'Reversed' => 'success',
                                            'Partially Invoiced' => 'info',
                                            default => 'gray',
                                        }),
                                    TextEntry::make('amount_expense_actual')
                                        ->label('Act. Expense')
                                        ->money('IDR'),
                                    TextEntry::make('amount_actual')
                                        ->label('Act. Revenue')
                                        ->money('IDR')
                                        ->weight('bold')
                                        ->color('primary'),
                                ]),

                            RepeatableEntry::make('accrueInvoiceMappings')
                                ->label('Associated Invoices')
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextEntry::make('invoice.number')
                                            ->label('Invoice Number')
                                            ->weight('bold'),
                                        TextEntry::make('allocated_amount')
                                            ->label('Allocated')
                                            ->money('IDR'),
                                        TextEntry::make('status')
                                            ->label('Reversal')
                                            ->badge(),
                                    ]),
                                ])
                                ->columnSpanFull()
                                ->visible(fn ($record) => $record?->accrueInvoiceMappings()->exists()),

                            TextEntry::make('description')
                                ->markdown(),
                        ])
                        ->columnSpanFull(),
                ]),

            Section::make('Summary')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('total_amount_expense_estimated')
                                ->label('Total Estimated Expense')
                                ->money('IDR')
                                ->weight('bold'),
                            TextEntry::make('total_amount_estimated')
                                ->label('Total Estimated Revenue')
                                ->money('IDR')
                                ->weight('bold'),
                            TextEntry::make('total_amount_expense_actual')
                                ->label('Total Actual Expense')
                                ->money('IDR')
                                ->weight('bold'),
                            TextEntry::make('total_amount_actual')
                                ->label('Total Actual Revenue')
                                ->money('IDR')
                                ->weight('bold')
                                ->color('success'),
                        ]),
                    TextEntry::make('description')
                        ->label('General Notes')
                        ->markdown()
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
