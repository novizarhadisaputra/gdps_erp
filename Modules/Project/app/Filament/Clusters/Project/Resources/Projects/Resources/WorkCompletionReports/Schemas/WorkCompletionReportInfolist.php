<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class WorkCompletionReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Documents')
                    ->description('Essential project documentation and signed reports.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('draft_report_document')
                                    ->label('Draft BAPP (Unsigned)')
                                    ->state(fn ($record) => $record?->getFirstMedia('draft_report')?->file_name ?? 'No Document')
                                    ->url(function ($record) {
                                        $media = $record?->getFirstMedia('draft_report');
                                        if (! $media) {
                                            return null;
                                        }

                                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                                    }, true)
                                    ->icon(Heroicon::OutlinedDocumentText)
                                    ->color(fn ($state) => $state === 'No Document' ? 'gray' : 'primary'),
                                TextEntry::make('signed_report_document')
                                    ->label('Signed BAPP (Final Scan)')
                                    ->state(fn ($record) => $record?->getFirstMedia('signed_report')?->file_name ?? 'No Document')
                                    ->url(function ($record) {
                                        $media = $record?->getFirstMedia('signed_report');
                                        if (! $media) {
                                            return null;
                                        }

                                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                                    }, true)
                                    ->icon(Heroicon::OutlinedCheckBadge)
                                    ->color(fn ($state) => $state === 'No Document' ? 'gray' : 'success'),
                            ]),
                    ])->columnSpanFull(),

                Section::make('Report Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('number')
                                    ->label('Report Number')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('document_date')
                                    ->date(),
                                TextEntry::make('project.name')
                                    ->label('Project'),
                                TextEntry::make('customer.name')
                                    ->label('Customer'),
                                TextEntry::make('service_period_start')
                                    ->label('Start Date')
                                    ->date(),
                                TextEntry::make('service_period_end')
                                    ->label('End Date')
                                    ->date(),
                                TextEntry::make('work_progress_percentage')
                                    ->label('Progress')
                                    ->suffix('%')
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('status')
                                    ->badge(),
                            ]),
                    ])->columnSpanFull(),

                Section::make('BAPP Line Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        TextEntry::make('item_name')
                                            ->label('Item Name')
                                            ->columnSpan(2),
                                        TextEntry::make('quantity')
                                            ->numeric(),
                                        TextEntry::make('unit_price')
                                            ->money('IDR'),
                                        TextEntry::make('total_price')
                                            ->money('IDR')
                                            ->weight(FontWeight::Bold),
                                    ]),
                            ])
                            ->columnSpanFull(),
                        TextEntry::make('total_amount')
                            ->label('Grand Total')
                            ->money('IDR')
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->alignEnd(),
                    ])->columnSpanFull(),

                Section::make('Communication History')
                    ->description('Traceability of emails sent to the customer regarding this BAPP.')
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
