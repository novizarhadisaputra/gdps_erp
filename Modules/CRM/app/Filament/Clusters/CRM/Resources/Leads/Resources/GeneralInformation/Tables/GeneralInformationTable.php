<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GeneralInformationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('scope_of_work')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('estimated_start_date')
                    ->date()
                    ->label(__('Start'))
                    ->sortable(),
                TextColumn::make('estimated_end_date')
                    ->date()
                    ->label(__('End'))
                    ->sortable(),
                IconColumn::make('tor')
                    ->label(__('ToR'))
                    ->getStateUsing(fn ($record) => $record?->hasMedia('tor'))
                    ->boolean()
                    ->url(function ($record) {
                        $media = $record->getFirstMedia('tor');
                        if (! $media) {
                            return null;
                        }

                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                    }, true),
                IconColumn::make('rfp')
                    ->label(__('RFP'))
                    ->getStateUsing(fn ($record) => $record?->hasMedia('rfp'))
                    ->boolean()
                    ->url(function ($record) {
                        $media = $record->getFirstMedia('rfp');
                        if (! $media) {
                            return null;
                        }

                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                    }, true),
                IconColumn::make('rfq')
                    ->label(__('RFQ'))
                    ->getStateUsing(fn ($record) => $record?->hasMedia('rfq'))
                    ->boolean()
                    ->url(function ($record) {
                        $media = $record->getFirstMedia('rfq');
                        if (! $media) {
                            return null;
                        }

                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                    }, true),
                IconColumn::make('rr_document_path')
                    ->label(__('RR Doc'))
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('success')
                    ->url(fn ($record) => $record?->rr_document_path, true)
                    ->visible(fn ($record) => filled($record?->rr_document_path)),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
