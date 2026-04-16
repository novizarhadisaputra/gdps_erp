<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Traits\HasProfitabilityAnalysisActions;

class ProfitabilityAnalysesTable
{
    use HasProfitabilityAnalysisActions;

    public static function configure(Table $table): Table
    {
        $instance = new self;

        return $table
            ->columns([
                TextColumn::make('document_number')
                    ->label('Document No.')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('proposal.proposal_number')
                    ->label('Proposal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('workScheme.name')
                    ->label('Scheme')
                    ->sortable(),
                TextColumn::make('revenue_per_month')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('margin_percentage')
                    ->label('Margin')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn (float $state): string => $state < 10 ? 'danger' : ($state < 20 ? 'warning' : 'success')),
                TextColumn::make('status')
                    ->badge(),
                IconColumn::make('tor')
                    ->label('ToR')
                    ->getStateUsing(fn ($record) => $record->hasMedia('tor'))
                    ->boolean()
                    ->url(function ($record) {
                        $media = $record->getFirstMedia('tor');
                        if (! $media) {
                            return null;
                        }

                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                    }, true)
                    ->toggleable(),
                IconColumn::make('rfp')
                    ->label('RFP')
                    ->getStateUsing(fn ($record) => $record->hasMedia('rfp'))
                    ->boolean()
                    ->url(function ($record) {
                        $media = $record->getFirstMedia('rfp');
                        if (! $media) {
                            return null;
                        }

                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                    }, true)
                    ->toggleable(),
                IconColumn::make('rfq')
                    ->label('RFQ')
                    ->getStateUsing(fn ($record) => $record->hasMedia('rfq'))
                    ->boolean()
                    ->url(function ($record) {
                        $media = $record->getFirstMedia('rfq');
                        if (! $media) {
                            return null;
                        }

                        return $media->disk === 's3' ? $media->getTemporaryUrl(now()->addMinutes(30)) : $media->getUrl();
                    }, true)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name'),
                SelectFilter::make('status')
                    ->options(ProfitabilityAnalysisStatus::class),
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalFooterActions($instance->getProfitabilityAnalysisActions()),
                EditAction::make(),
                ActionGroup::make($instance->getStepActions())
                    ->label('Steps')
                    ->icon(Heroicon::ListBullet)
                    ->color('info')
                    ->button(),
                $instance->getDuplicateAction(),
                $instance->getWeeklyRevenueUpdateAction(),
                $instance->getCreateProposalAction(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
