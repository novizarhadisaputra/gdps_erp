<?php

namespace Modules\CRM\Filament\Resources\Proposals\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class ProposalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('client.name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('proposal_number')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'converted' => 'warning',
                    }),
                \Filament\Tables\Columns\TextColumn::make('submission_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('createPA')
                    ->label('Create Profitability Analysis')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->color('info')
                    ->visible(fn (\Modules\CRM\Models\Proposal $record): bool => $record->status === 'approved')
                    ->form([
                        \Filament\Forms\Components\Select::make('work_scheme_id')
                            ->relationship('workScheme', 'name', modifyQueryUsing: fn ($query) => $query->from('work_schemes'))
                            ->label('Select Work Scheme')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (\Modules\CRM\Models\Proposal $record, array $data) {
                        $existingPa = \Modules\Finance\Models\ProfitabilityAnalysis::where('proposal_id', $record->id)->first();

                        if ($existingPa) {
                            \Filament\Notifications\Notification::make()
                                ->title('PA Already Exists')
                                ->body('Redirecting to the existing Profitability Analysis.')
                                ->warning()
                                ->send();

                            return redirect(\Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource::getUrl('edit', ['record' => $existingPa]));
                        }

                        $pa = \Modules\Finance\Models\ProfitabilityAnalysis::create([
                            'proposal_id' => $record->id,
                            'client_id' => $record->client_id,
                            'work_scheme_id' => $data['work_scheme_id'],
                            'status' => 'draft',
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Profitability Analysis Created')
                            ->success()
                            ->send();

                        return redirect(\Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource::getUrl('edit', ['record' => $pa]));
                    }),
                Action::make('convertToContract')
                    ->label('Convert to Contract')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->visible(fn (\Modules\CRM\Models\Proposal $record): bool => $record->status === 'approved')
                    ->requiresConfirmation()
                    ->action(function (\Modules\CRM\Models\Proposal $record) {
                        $contract = \Modules\CRM\Models\Contract::create([
                            'client_id' => $record->client_id,
                            'proposal_id' => $record->id,
                            'contract_number' => 'CONTRACT-'.$record->proposal_number,
                            'status' => 'draft',
                        ]);

                        $record->update(['status' => 'converted']);

                        \Filament\Notifications\Notification::make()
                            ->title('Converted to Contract')
                            ->success()
                            ->send();

                        return redirect(\Modules\CRM\Filament\Resources\Contracts\ContractResource::getUrl('edit', ['record' => $contract]));
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
