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
                Section::make(__('Proposal Details'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('title')
                                    ->label(__('Proposal Title'))
                                    ->weight(FontWeight::Bold)
                                    ->columnSpanFull(),
                                TextEntry::make('status')
                                    ->badge(),
                                TextEntry::make('number')
                                    ->label(__('Proposal Number'))
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('customer.name')
                                    ->label(__('Customer')),
                                TextEntry::make('profitabilityAnalysis.number')
                                    ->label(__('Profitability Analysis (PA)'))
                                    ->color('primary')
                                    ->weight(FontWeight::Bold)
                                    ->url(fn ($record) => $record?->profitability_analysis_id ? \Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource::getUrl('view', ['record' => $record->profitability_analysis_id]) : null),
                                TextEntry::make('workScheme.name')
                                    ->label(__('Work Scheme')),
                                TextEntry::make('product_cluster_id')
                                    ->label(__('Product Cluster'))
                                    ->state(fn ($record) => $record->profitabilityAnalysis?->productCluster?->name ?? $record->lead?->productCluster?->name ?? '-')
                                    ->badge(),
                                TextEntry::make('amount')
                                    ->money('IDR'),
                                TextEntry::make('submission_date')
                                    ->date(),
                                TextEntry::make('meeting_date')
                                    ->date()
                                    ->color('info')
                                    ->visible(fn ($record) => filled($record?->meeting_date)),
                                TextEntry::make('revision_number')
                                    ->label(__('Revision #'))
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('previous_code')
                                    ->label(__('Previous Code'))
                                    ->visible(fn ($record) => filled($record?->previous_code)),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('final_proposal_document')
                                    ->label(__('Proposal Document'))
                                    ->state(fn ($record) => $record?->getFirstMedia('final_proposal')?->file_name ?? 'No Document')
                                    ->url(function ($record) {
                                        $media = $record?->getFirstMedia('final_proposal');
                                        if (! $media) {
                                            return null;
                                        }

                                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                                    }, true)
                                    ->icon(Heroicon::OutlinedDocumentText)
                                    ->color(fn ($state) => $state === 'No Document' ? 'gray' : 'primary'),
                                TextEntry::make('signed_proposal_document')
                                    ->label(__('Signed Proposal'))
                                    ->state(fn ($record) => $record?->getFirstMedia('signed_proposal')?->file_name ?? 'No Document')
                                    ->url(function ($record) {
                                        $media = $record?->getFirstMedia('signed_proposal');
                                        if (! $media) {
                                            return null;
                                        }

                                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                                    }, true)
                                    ->icon(Heroicon::OutlinedCheckBadge)
                                    ->color(fn ($state) => $state === 'No Document' ? 'gray' : 'success'),
                            ]),
                    ])->columnSpanFull(),
                Section::make(__('Approval & Signatures'))
                    ->schema([
                        DigitalSignatureEntry::make('signatures')
                            ->columnSpanFull(),
                    ])->columnSpanFull(),

                Section::make(__('Discussions'))
                    ->description(__('Internal team notes and revision context.'))
                    ->schema([
                        RepeatableEntry::make('comments')
                            ->label(__('Internal Notes'))
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('user.name')
                                        ->label(__('User'))
                                        ->icon('heroicon-m-user'),
                                    TextEntry::make('body')
                                        ->label(__('Comment'))
                                        ->columnSpan(2),
                                    TextEntry::make('created_at')
                                        ->label(__('Date'))
                                        ->dateTime()
                                        ->color('gray'),
                                ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record?->comments()->exists()),

                Section::make(__('Communication History'))
                    ->description(__('Traceability of emails sent to the customer.'))
                    ->schema([
                        RepeatableEntry::make('communicationLogs')
                            ->label(__('Sent Emails'))
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('recipient_email')
                                        ->label(__('Recipient'))
                                        ->icon('heroicon-m-envelope'),
                                    TextEntry::make('subject')
                                        ->label(__('Subject')),
                                    TextEntry::make('sent_by')
                                        ->label(__('Sent By'))
                                        ->state(fn ($record) => $record->sender?->name ?? $record->sender_email ?? '-'),
                                    TextEntry::make('sent_at')
                                        ->label(__('Sent Date'))
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
