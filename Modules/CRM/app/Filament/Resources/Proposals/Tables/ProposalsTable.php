<?php

namespace Modules\CRM\Filament\Resources\Proposals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
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
