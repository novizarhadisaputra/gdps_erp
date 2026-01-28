<?php

namespace Modules\CRM\Filament\Resources\Contracts\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Models\Contract;

class ContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contract_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('proposal.proposal_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('reminder_status')
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', $state)),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalFooterActions([
                        Action::make('Activate')
                            ->color('success')
                            ->icon('heroicon-o-check-circle')
                            ->requiresConfirmation()
                            ->action(fn (Contract $record) => $record->update(['status' => ContractStatus::Active]))
                            ->visible(fn (Contract $record) => $record->status === ContractStatus::Draft),

                        Action::make('Terminate')
                            ->color('danger')
                            ->icon('heroicon-o-x-circle')
                            ->requiresConfirmation()
                            ->form([
                                Textarea::make('termination_reason')
                                    ->label('Reason for Termination')
                                    ->required(),
                            ])
                            ->action(fn (Contract $record, array $data) => $record->update([
                                'status' => ContractStatus::Terminated,
                                'termination_reason' => $data['termination_reason'],
                            ]))
                            ->visible(fn (Contract $record) => $record->status === ContractStatus::Active),

                        Action::make('Mark Expired')
                            ->color('warning')
                            ->icon('heroicon-o-clock')
                            ->requiresConfirmation()
                            ->action(fn (Contract $record) => $record->update(['status' => ContractStatus::Expired]))
                            ->visible(fn (Contract $record) => $record->status === ContractStatus::Active),
                    ]),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
