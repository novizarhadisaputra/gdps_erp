<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Schemas;

use App\Filament\Infolists\Components\DigitalSignatureEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class ProposalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Proposal Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Proposal Title')
                                    ->weight(FontWeight::Bold)
                                    ->columnSpanFull(),
                                TextEntry::make('proposal_number')
                                    ->label('Proposal Number')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('customer.name')
                                    ->label('Customer'),
                                TextEntry::make('amount')
                                    ->money('IDR'),
                                TextEntry::make('submission_date')
                                    ->date(),
                                TextEntry::make('revision_number')
                                    ->label('Revision #')
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('previous_code')
                                    ->label('Previous Code')
                                    ->visible(fn ($record) => filled($record->previous_code)),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('final_proposal_document')
                                    ->label('Proposal Document')
                                    ->state(fn ($record) => $record->getFirstMedia('final_proposal')?->file_name ?? 'No Document')
                                    ->url(function ($record) {
                                        $media = $record->getFirstMedia('final_proposal');
                                        if (! $media) {
                                            return null;
                                        }

                                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                                    }, true)
                                    ->icon(Heroicon::OutlinedDocumentText)
                                    ->color(fn ($state) => $state === 'No Document' ? 'gray' : 'primary'),
                                TextEntry::make('signed_proposal_document')
                                    ->label('Signed Proposal')
                                    ->state(fn ($record) => $record->getFirstMedia('signed_proposal')?->file_name ?? 'No Document')
                                    ->url(function ($record) {
                                        $media = $record->getFirstMedia('signed_proposal');
                                        if (! $media) {
                                            return null;
                                        }

                                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                                    }, true)
                                    ->icon(Heroicon::OutlinedCheckBadge)
                                    ->color(fn ($state) => $state === 'No Document' ? 'gray' : 'success'),
                            ]),
                    ])->columnSpanFull(),
                Section::make('Approval & Signatures')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('status')
                                    ->badge(),
                            ]),
                        DigitalSignatureEntry::make('signatures')
                            ->columnSpanFull(),
                    ])->columnSpanFull()
                    ->visible(fn ($record) => $record->signatures()->exists()),

                Section::make('Discussions')
                    ->description('Internal team notes and revision context.')
                    ->schema([
                        RepeatableEntry::make('comments')
                            ->label('Internal Notes')
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('user.name')
                                        ->label('User')
                                        ->icon('heroicon-m-user'),
                                    TextEntry::make('body')
                                        ->label('Comment')
                                        ->columnSpan(2),
                                    TextEntry::make('created_at')
                                        ->label('Date')
                                        ->dateTime()
                                        ->color('gray'),
                                ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->visible(fn ($record) => $record->comments()->exists()),

                Section::make('Communication History')
                    ->description('Traceability of emails sent to the customer.')
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
                                    TextEntry::make('sent_by')
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
                    ->collapsible()
                    ->visible(fn ($record) => $record->communicationLogs()->exists()),
            ]);
    }
}
