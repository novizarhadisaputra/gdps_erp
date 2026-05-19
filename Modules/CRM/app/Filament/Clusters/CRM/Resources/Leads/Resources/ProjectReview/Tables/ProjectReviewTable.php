<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectReviewTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label(__('Review Code'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                TextColumn::make('lead.company_name')
                    ->label(__('Lead / Company'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('generalInformation.number')
                    ->label(__('GI #'))
                    ->placeholder(__('-'))
                    ->searchable(),
                TextColumn::make('profitabilityAnalysis.number')
                    ->label(__('PA #'))
                    ->placeholder(__('-'))
                    ->searchable(),
                TextColumn::make('proposal.number')
                    ->label(__('Proposal #'))
                    ->placeholder(__('-'))
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
