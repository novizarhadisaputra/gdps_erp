<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Enums\ConfidenceLevel;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Models\SalesPlan;

class SalesPlanTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lead.title')
                    ->label('Lead')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ams.name')
                    ->label('AMS')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('revenueSegment.name')
                    ->label('Segment')
                    ->sortable(),
                TextColumn::make('productCluster.name')
                    ->label('Cluster')
                    ->sortable(),
                TextColumn::make('paymentTerm.name')
                    ->label('ToP')
                    ->sortable(),
                TextColumn::make('estimated_value')
                    ->label('Value')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('confidence_level')
                    ->label('Confidence')
                    ->badge()
                    ->color(fn (ConfidenceLevel $state): string => match ($state) {
                        ConfidenceLevel::Optimistic => 'success',
                        ConfidenceLevel::Moderate => 'warning',
                        ConfidenceLevel::Pessimistic => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('so_number')
                    ->label('SO #')
                    ->searchable()
                    ->placeholder('N/A')
                    ->toggleable(),
                TextColumn::make('ba_number')
                    ->label('BAPP #')
                    ->searchable()
                    ->placeholder('N/A')
                    ->toggleable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    Action::make('convertToGI')
                        ->label('Convert to GI')
                        ->icon(Heroicon::OutlinedDocumentPlus)
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (SalesPlan $record) => $record->lead?->generalInformations()->doesntExist())
                        ->action(function (SalesPlan $record) {
                            $lead = $record->lead;

                            if (! $lead) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Lead not found for this Sales Plan.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $record->toGeneralInformation();

                            Notification::make()
                                ->title('General Information Created')
                                ->body('Data has been synced from Sales Plan.')
                                ->success()
                                ->send();

                            return redirect()->to(LeadResource::getUrl('general-informations', ['record' => $lead]));
                        }),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->color('gray')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
