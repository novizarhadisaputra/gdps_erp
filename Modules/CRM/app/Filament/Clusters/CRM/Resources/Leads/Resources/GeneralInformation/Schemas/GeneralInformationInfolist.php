<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Schemas;

use App\Filament\Infolists\Components\DigitalSignatureEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GeneralInformationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('General Information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('number'),
                                TextEntry::make('customer.name')
                                    ->label(__('Customer')),
                                TextEntry::make('status')
                                    ->badge(),
                                TextEntry::make('scope_of_work')
                                    ->columnSpanFull(),
                                TextEntry::make('location'),
                                TextEntry::make('projectArea.name')
                                    ->label(__('Project Area')),
                                TextEntry::make('workScheme.name')
                                    ->label(__('Work Scheme')),
                                TextEntry::make('productCluster.name')
                                    ->label(__('Product Cluster')),
                                TextEntry::make('salesPlan.project_code')
                                    ->label(__('Source Sales Plan'))
                                    ->placeholder(__('No Project Code Linked')),
                            ]),
                        TextEntry::make('description')
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('estimated_start_date')
                                    ->date(),
                                TextEntry::make('estimated_end_date')
                                    ->date(),
                                TextEntry::make('manpower_qualifications')
                                    ->columnSpanFull(),
                                TextEntry::make('work_activities')
                                    ->columnSpanFull(),
                                TextEntry::make('service_level')
                                    ->columnSpanFull(),
                                TextEntry::make('billing_requirements')
                                    ->columnSpanFull(),
                                TextEntry::make('risk_management')
                                    ->badge()
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull(),

                Section::make(__('Documentation'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tor')
                                    ->label(__('ToR Document'))
                                    ->state(fn ($record) => $record?->getFirstMedia('tor')?->file_name)
                                    ->url(function ($record) {
                                        $media = $record?->getFirstMedia('tor');
                                        if (! $media) {
                                            return null;
                                        }

                                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                                    }, true)
                                    ->visible(fn ($record) => $record?->hasMedia('tor')),
                                TextEntry::make('rfp')
                                    ->label(__('RFP Document'))
                                    ->state(fn ($record) => $record?->getFirstMedia('rfp')?->file_name)
                                    ->url(function ($record) {
                                        $media = $record?->getFirstMedia('rfp');
                                        if (! $media) {
                                            return null;
                                        }

                                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                                    }, true)
                                    ->visible(fn ($record) => $record?->hasMedia('rfp')),
                                TextEntry::make('rfq')
                                    ->label(__('RFQ Document'))
                                    ->state(fn ($record) => $record?->getFirstMedia('rfq')?->file_name)
                                    ->url(function ($record) {
                                        $media = $record->getFirstMedia('rfq');
                                        if (! $media) {
                                            return null;
                                        }

                                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                                    }, true)
                                    ->visible(fn ($record) => $record?->hasMedia('rfq')),
                                TextEntry::make('other_documents')
                                    ->label(__('Other Documents'))
                                    ->html()
                                    ->formatStateUsing(function ($record) {
                                        if (! $record) {
                                            return null;
                                        }

                                        return $record->getMedia('other_documents')->map(function ($media) {
                                            $url = $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();

                                            return "<a href='{$url}' target='_blank' class='text-primary-600 hover:underline'>{$media->file_name}</a>";
                                        })->join('<br>');
                                    })
                                    ->visible(fn ($record) => $record?->hasMedia('other_documents')),
                            ]),
                    ])->columnSpanFull(),

                Section::make(__('PICs'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('pics.name')
                                    ->label(__('Name'))
                                    ->listWithLineBreaks()
                                    ->bulleted(),
                                TextEntry::make('pics.jobPosition.name')
                                    ->label(__('Job Position'))
                                    ->listWithLineBreaks()
                                    ->bulleted(),
                                TextEntry::make('pics.contactRole.name')
                                    ->label(__('Role'))
                                    ->listWithLineBreaks()
                                    ->bulleted(),
                                TextEntry::make('pics.email')
                                    ->label(__('Email'))
                                    ->listWithLineBreaks()
                                    ->bulleted(),
                                TextEntry::make('pics.phone')
                                    ->label(__('Phone'))
                                    ->listWithLineBreaks()
                                    ->bulleted(),
                            ]),
                    ])->columnSpanFull(),
                Section::make(__('Risk Register'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('rr_status')
                                    ->label(__('RR Status'))
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'approved' => 'success',
                                        'submitted', 'pending' => 'info',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '-'),
                                TextEntry::make('rr_document_number')
                                    ->label(__('RR Document Number'))
                                    ->placeholder(__('-')),
                                TextEntry::make('rr_submission_id')
                                    ->label(__('RR Submission ID'))
                                    ->placeholder(__('-')),
                                TextEntry::make('rr_document_path')
                                    ->label(__('RR Document'))
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->color('primary')
                                    ->formatStateUsing(fn () => 'Download Document')
                                    ->url(fn ($record) => $record?->rr_document_path, true)
                                    ->visible(fn ($record) => filled($record?->rr_document_path)),
                            ]),
                    ])->columnSpanFull()
                    ->visible(fn ($record) => filled($record?->rr_status)),
                Section::make(__('Approval & Signatures'))
                    ->schema([
                        DigitalSignatureEntry::make('signatures')
                            ->columnSpanFull(),
                    ])->columnSpanFull()
                    ->visible(fn ($record) => $record?->signatures()->exists()),

                Section::make(__('Remarks'))
                    ->schema([
                        TextEntry::make('remarks')
                            ->hiddenLabel()
                            ->placeholder(__('No additional remarks')),
                    ])->columnSpanFull()
                    ->visible(fn ($record) => filled($record?->remarks)),
            ]);
    }
}
