<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Models\Contract;
use Modules\Project\Services\ProjectService;

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
                    ->formatStateUsing(fn ($state): string => str_replace('_', ' ', $state instanceof \BackedEnum ? $state->value : (string) $state)),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('renew')
                    ->label('Renew')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Contract $record) {
                        $renewal = $record->replicate();
                        $renewal->status = ContractStatus::Draft;
                        $renewal->contract_number = $renewal->contract_number.'-RENEW';
                        $renewal->save();

                        Notification::make()
                            ->title('Contract Renewed')
                            ->body('A project sequence increment will apply when you generate a project for this new contract.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Contract $record) => $record->status === ContractStatus::Active || $record->status === ContractStatus::Expired),
                Action::make('handoverToProject')
                    ->label('Handover to Project')
                    ->icon(Heroicon::OutlinedRocketLaunch)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Contract $record, ProjectService $service) {
                        $project = $service->attemptProjectCreation($record->proposal);

                        if ($project) {
                            Notification::make()
                                ->title('Project Created Successfully')
                                ->body("Project {$project->code} has been created and initialized.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Project Creation Failed')
                                ->body('Unable to create project. Please ensure: 1. A signed proposal file is uploaded. 2. Profitability Analysis is fully approved.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Contract $record) => $record->project()->doesntExist() && $record->proposal_id !== null),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
