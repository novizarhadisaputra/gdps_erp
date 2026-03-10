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
                Section::make('General Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('document_number'),
                                TextEntry::make('customer.name')
                                    ->label('Customer'),
                                TextEntry::make('status')
                                    ->badge(),
                                TextEntry::make('scope_of_work')
                                    ->columnSpanFull(),
                                TextEntry::make('location'),
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

                Section::make('Documentation')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tor')
                                    ->label('ToR Document')
                                    ->state(fn ($record) => $record->getFirstMedia('tor')?->file_name)
                                    ->url(function ($record) {
                                        $media = $record->getFirstMedia('tor');
                                        if (! $media) {
                                            return null;
                                        }

                                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                                    }, true)
                                    ->visible(fn ($record) => $record->hasMedia('tor')),
                                TextEntry::make('rfp')
                                    ->label('RFP Document')
                                    ->state(fn ($record) => $record->getFirstMedia('rfp')?->file_name)
                                    ->url(function ($record) {
                                        $media = $record->getFirstMedia('rfp');
                                        if (! $media) {
                                            return null;
                                        }

                                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                                    }, true)
                                    ->visible(fn ($record) => $record->hasMedia('rfp')),
                                TextEntry::make('rfi')
                                    ->label('RFI Document')
                                    ->state(fn ($record) => $record->getFirstMedia('rfi')?->file_name)
                                    ->url(function ($record) {
                                        $media = $record->getFirstMedia('rfi');
                                        if (! $media) {
                                            return null;
                                        }

                                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                                    }, true)
                                    ->visible(fn ($record) => $record->hasMedia('rfi')),
                            ]),
                    ])->columnSpanFull(),

                Section::make('PICs')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('pics.name')
                                    ->label('Name')
                                    ->listWithLineBreaks()
                                    ->bulleted(),
                                TextEntry::make('pics.contactRole.name')
                                    ->label('Role')
                                    ->listWithLineBreaks()
                                    ->bulleted(),
                                TextEntry::make('pics.email')
                                    ->label('Email')
                                    ->listWithLineBreaks()
                                    ->bulleted(),
                                TextEntry::make('pics.phone')
                                    ->label('Phone')
                                    ->listWithLineBreaks()
                                    ->bulleted(),
                            ]),
                    ])->columnSpanFull(),
                Section::make('Risk Register')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('rr_status')
                                    ->label('RR Status')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'approved' => 'success',
                                        'submitted', 'pending' => 'info',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '-'),
                                TextEntry::make('rr_document_number')
                                    ->label('RR Document Number')
                                    ->placeholder('-'),
                                TextEntry::make('rr_submission_id')
                                    ->label('RR Submission ID')
                                    ->placeholder('-'),
                                TextEntry::make('rr_document_path')
                                    ->label('RR Document')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->color('primary')
                                    ->formatStateUsing(fn () => 'Download Document')
                                    ->url(fn ($record) => $record->rr_document_path, true)
                                    ->visible(fn ($record) => filled($record->rr_document_path)),
                            ]),
                    ])->columnSpanFull()
                    ->visible(fn ($record) => filled($record?->rr_status)),
                Section::make('Approval & Signatures')
                    ->schema([
                        DigitalSignatureEntry::make('signatures')
                            ->columnSpanFull(),
                    ])->columnSpanFull()
                    ->visible(fn ($record) => $record?->signatures()->exists()),
            ]);
    }
}
