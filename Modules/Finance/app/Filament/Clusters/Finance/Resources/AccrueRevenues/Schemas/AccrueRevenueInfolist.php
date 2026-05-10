<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Modules\MasterData\Services\SignatureService;

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

            Section::make('Approval History')
                ->description('Digital signatures recorded during the approval process.')
                ->schema([
                    RepeatableEntry::make('signatures')
                        ->label('')
                        ->schema([
                            Grid::make(4)->schema([
                                TextEntry::make('user.name')
                                    ->label('Approver Name')
                                    ->icon('heroicon-m-user'),
                                TextEntry::make('role')
                                    ->label('Title/Role')
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('signed_at')
                                    ->label('Signed At')
                                    ->dateTime()
                                    ->icon('heroicon-m-calendar'),
                                TextEntry::make('qr_code')
                                    ->label('QR Verification')
                                    ->state(function ($record) {
                                        $service = app(SignatureService::class);
                                        $url = $service->createSignatureData($record->user, $record->signable, $record->signature_type);
                                        $qr = $service->generateQRCode($url);

                                        return new HtmlString("<img src='{$qr}' style='height: 60px; width: 60px; border: 1px solid #eee; padding: 2px;' />");
                                    })
                                    ->html(),
                            ]),
                        ])
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn ($record) => $record?->signatures()->exists()),
        ]);
    }
}
