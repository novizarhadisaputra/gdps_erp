<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;
use Modules\MasterData\Services\SignatureService;

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
                                TextEntry::make('number')
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
                                TextEntry::make('salesOrder.number')
                                    ->label('Sales Order'),
                                TextEntry::make('workCompletionReport.number')
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
                                    ->label('Tax')
                                    ->money('IDR'),
                                TextEntry::make('total_amount')
                                    ->label('Total Amount')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),
                            ]),
                    ])->columnSpanFull(),

                Section::make('Linked Accruals')
                    ->description('Accruals associated with this invoice that will be reversed upon approval.')
                    ->schema([
                        RepeatableEntry::make('accrueInvoiceMappings')
                            ->label('')
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('accrueRevenueItem.accrueRevenue.number')
                                        ->label('Accrual Number')
                                        ->weight(FontWeight::Bold),
                                    TextEntry::make('accrueRevenueItem.revenue_type')
                                        ->label('Revenue Type'),
                                    TextEntry::make('allocated_amount')
                                        ->label('Allocated Amount')
                                        ->money('IDR'),
                                    TextEntry::make('status')
                                        ->label('Reversal Status')
                                        ->badge(),
                                ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record?->accrueInvoiceMappings()->exists()),

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
                            ->label('Account Name (a.n.)'),
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

                                return new HtmlString("<a href='{$media->getUrl()}' target='_blank' class='text-primary-600 font-bold underline flex items-center gap-1'>Lihat Bukti Pembayaran ({$media->file_name})</a>");
                            })
                            ->html(),
                    ])->columnSpanFull()
                    ->visible(fn ($record) => $record?->hasMedia('payment_proof')),

                Section::make('Documents')
                    ->schema([
                        TextEntry::make('signed_invoice')
                            ->label('Signed Invoice (Final)')
                            ->state(function ($record) {
                                $media = $record->getFirstMedia('signed_invoice');
                                if (! $media) {
                                    return 'Dokumen belum diunggah.';
                                }

                                return new HtmlString("<a href='{$media->getUrl()}' target='_blank' class='text-primary-600 font-bold underline flex items-center gap-1'>Lihat Signed Invoice ({$media->file_name})</a>");
                            })
                            ->html(),
                    ])->columnSpanFull()
                    ->visible(fn ($record) => $record?->hasMedia('signed_invoice')),

                Section::make('Approval History')
                    ->description('Digital signatures recorded during the approval process.')
                    ->schema([
                        RepeatableEntry::make('signatures')
                            ->label('')
                            ->schema([
                                Grid::make(3)->schema([
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
