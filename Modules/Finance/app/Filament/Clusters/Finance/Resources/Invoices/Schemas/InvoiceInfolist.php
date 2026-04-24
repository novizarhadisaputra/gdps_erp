<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('invoice_number')
                                    ->label('Invoice Number')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('status')
                                    ->badge(),
                                TextEntry::make('invoice_date')
                                    ->date(),
                                TextEntry::make('due_date')
                                    ->date()
                                    ->color('danger'),
                                TextEntry::make('customer.name')
                                    ->label('Customer'),
                                TextEntry::make('salesOrder.so_number')
                                    ->label('Sales Order'),
                                TextEntry::make('workCompletionReport.report_number')
                                    ->label('Work Completion Report (BAPP)'),
                            ]),
                    ])->columnSpanFull(),

                Section::make('Financial Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('amount')
                                    ->label('Base Amount')
                                    ->money('IDR'),
                                TextEntry::make('tax_amount')
                                    ->label('Tax (11%)')
                                    ->money('IDR'),
                                TextEntry::make('total_amount')
                                    ->label('Total Amount')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),
                            ]),
                    ])->columnSpanFull(),

                Section::make('Invoice Line Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('Line Items')
                            ->schema([
                                Grid::make(5)->schema([
                                    TextEntry::make('item_name')
                                        ->label('Item Name')
                                        ->columnSpan(2),
                                    TextEntry::make('quantity')
                                        ->label('Quantity'),
                                    TextEntry::make('uom')
                                        ->label('Unit'),
                                    TextEntry::make('unit_price')
                                        ->label('Price / Unit')
                                        ->money('IDR'),
                                    TextEntry::make('total_price')
                                        ->label('Total Price')
                                        ->money('IDR')
                                        ->weight(FontWeight::Bold),
                                ]),
                            ])
                            ->columnSpanFull(),
                    ])->columnSpanFull(),

                Section::make('Payment Information')
                    ->schema([
                        TextEntry::make('payment_info.account_name')
                            ->label('Account Name (A/N)'),
                        RepeatableEntry::make('payment_info.banks')
                            ->label('Bank Accounts')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextEntry::make('bank_name')->label('Bank Name'),
                                    TextEntry::make('account_number')->label('Account Number'),
                                    TextEntry::make('currency')->label('Currency'),
                                ]),
                            ])
                            ->columnSpanFull(),
                    ])->columnSpanFull(),

                Section::make('Attachments')
                    ->schema([
                        TextEntry::make('payment_proof')
                            ->label('Bukti Pembayaran')
                            ->state(function ($record) {
                                $media = $record->getFirstMedia('payment_proof');
                                if (! $media) {
                                    return 'Belum ada bukti pembayaran.';
                                }

                                return new \Illuminate\Support\HtmlString("<a href='{$media->getUrl()}' target='_blank' class='text-primary-600 font-bold underline flex items-center gap-1'>Lihat Bukti Pembayaran ({$media->file_name})</a>");
                            })
                            ->html(),
                    ])->columnSpanFull()
                    ->visible(fn ($record) => $record?->hasMedia('payment_proof')),

                Section::make('Communication History')
                    ->description('Traceability of emails sent to the customer regarding this invoice.')
                    ->schema([
                        RepeatableEntry::make('communicationLogs')
                            ->label('Sent Emails')
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('recipient_email')
                                        ->label('Recipient')
                                        ->icon('heroicon-m-envelope'),
                                    TextEntry::make('subject')
                                        ->label('Subject'),
                                    TextEntry::make('sender.name')
                                        ->label('Sent By')
                                        ->state(fn ($record) => $record->sender?->name ?? $record->sender_email ?? '-'),
                                    TextEntry::make('sent_at')
                                        ->label('Sent Date')
                                        ->dateTime()
                                        ->color('gray'),
                                ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record?->communicationLogs()->exists()),
            ]);
    }
}
