<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProfitabilityAnalysesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('proposal.proposal_number')
                    ->label('Proposal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Client')
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
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'approved' => 'info',
                        'converted' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('client_id')
                    ->relationship('client', 'name'),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'approved' => 'Approved',
                        'converted' => 'Converted',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('generateProject')
                    ->label('Generate Project')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->hidden(fn ($record) => $record->status === 'converted')
                    ->form([
                        \Filament\Forms\Components\Placeholder::make('summary')
                            ->content(fn ($record) => "You are about to generate a Project for '{$record->client?->name}'. This will consume the next sequence number for this client and work scheme."),
                        \Filament\Forms\Components\TextInput::make('project_name_override')
                            ->label('Project Name (Optional)')
                            ->placeholder(fn ($record) => $record->proposal?->proposal_number ?? 'Project for '.$record->client?->name),
                    ])
                    ->action(function ($record, array $data) {
                        $service = app(\Modules\Finance\Classes\ProjectGenerationService::class);

                        // We could pass the override name to the service if needed
                        $project = $service->generateFromPA($record);

                        if (! empty($data['project_name_override'])) {
                            $project->update(['name' => $data['project_name_override']]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Project Generated')
                            ->body("Project Code: {$project->code}")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
