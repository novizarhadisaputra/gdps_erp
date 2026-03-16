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
                TextColumn::make('lead.company_name')
                    ->label('Lead / Company')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('generalInformation.document_number')
                    ->label('GI #')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('profitabilityAnalysis.document_number')
                    ->label('PA #')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('proposal.proposal_number')
                    ->label('Proposal #')
                    ->placeholder('-')
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
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
