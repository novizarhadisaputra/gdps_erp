<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas;

use App\Filament\Infolists\Components\DigitalSignatureEntry;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class GeneralInformationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer & Project PIC')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('customer.name')
                                    ->label('Customer')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'draft' => 'gray',
                                        'submitted' => 'info',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),

                        RepeatableEntry::make('pics')
                            ->label('PICs')
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('phone'),
                                TextEntry::make('email'),
                                TextEntry::make('contactRole.name')->label('Role'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
                Section::make('Project Details')
                    ->schema([
                        TextEntry::make('scope_of_work')->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('estimated_start_date')->date(),
                                TextEntry::make('estimated_end_date')->date(),
                            ]),
                        TextEntry::make('location'),
                    ]),
                Section::make('Documents')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tor_document')
                                    ->label('ToR Document')
                                    ->state(fn ($record) => $record->getFirstMedia('tor')?->file_name ? 'ToR Document' : 'No Document')
                                    ->suffixAction(
                                        Action::make('preview_tor')
                                            ->label('Preview')
                                            ->color('info')
                                            ->modalContent(fn ($record) => view('crm::components.document-preview', [
                                                'url' => $record->getFirstMedia('tor')?->getTemporaryUrl(now()->addMinutes(30)),
                                                'type' => $record->getFirstMedia('tor')?->mime_type ?? 'application/pdf',
                                            ]))
                                            ->modalWidth('7xl')
                                            ->visible(fn ($record) => $record->hasMedia('tor'))
                                    )
                                    ->url(fn ($record) => $record->getFirstMedia('tor')?->getTemporaryUrl(now()->addMinutes(30)), true) // Keep download link
                                    ->icon(Heroicon::OutlinedDocumentText)
                                    ->color(fn ($state) => $state === 'No Document' ? 'gray' : 'primary'),

                                TextEntry::make('rfp_document')
                                    ->label('RFP Document')
                                    ->state(fn ($record) => $record->getFirstMedia('rfp')?->file_name ? 'RFP Document' : 'No Document')
                                    ->suffixAction(
                                        Action::make('preview_rfp')
                                            ->label('Preview')
                                            ->color('info')
                                            ->modalContent(fn ($record) => view('crm::components.document-preview', [
                                                'url' => $record->getFirstMedia('rfp')?->getTemporaryUrl(now()->addMinutes(30)),
                                                'type' => $record->getFirstMedia('rfp')?->mime_type ?? 'application/pdf',
                                            ]))
                                            ->modalWidth('7xl')
                                            ->visible(fn ($record) => $record->hasMedia('rfp'))
                                    )
                                    ->url(fn ($record) => $record->getFirstMedia('rfp')?->getTemporaryUrl(now()->addMinutes(30)), true)
                                    ->icon(Heroicon::OutlinedDocumentText)
                                    ->color(fn ($state) => $state === 'No Document' ? 'gray' : 'primary'),

                                TextEntry::make('rfi_document')
                                    ->label('RFI Document')
                                    ->state(fn ($record) => $record->getFirstMedia('rfi')?->file_name ? 'RFI Document' : 'No Document')
                                    ->suffixAction(
                                        Action::make('preview_rfi')
                                            ->label('Preview')->color('info')
                                            ->modalContent(fn ($record) => view('crm::components.document-preview', [
                                                'url' => $record->getFirstMedia('rfi')?->getTemporaryUrl(now()->addMinutes(30)),
                                                'type' => $record->getFirstMedia('rfi')?->mime_type ?? 'application/pdf',
                                            ]))
                                            ->modalWidth('7xl')
                                            ->visible(fn ($record) => $record->hasMedia('rfi'))
                                    )
                                    ->url(fn ($record) => $record->getFirstMedia('rfi')?->getTemporaryUrl(now()->addMinutes(30)), true)
                                    ->icon(Heroicon::OutlinedDocumentText)
                                    ->color(fn ($state) => $state === 'No Document' ? 'gray' : 'primary'),
                            ]),
                    ]),
                Section::make('Digital Signatures')
                    ->schema([
                        DigitalSignatureEntry::make('signatures')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->signatures()->exists()),

                Section::make('Risk Register (Raw Webhook Payload)')
                    ->description('The latest raw JSON data received from the Risk Register system.')
                    ->schema([
                        TextEntry::make('rr_payload')
                            ->label('')
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : 'No payload received yet.')
                            ->monospace()
                            ->extraAttributes(['class' => 'bg-gray-50 p-4 rounded-lg'])
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn ($record) => filled($record->rr_payload)),
            ]);
    }
}
