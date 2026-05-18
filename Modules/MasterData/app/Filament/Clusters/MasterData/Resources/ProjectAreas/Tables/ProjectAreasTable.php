<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Services\WilayahSyncService;

class ProjectAreasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('Code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('api_code')
                    ->label(__('Code (Official)'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('province.name')
                    ->label(__('Province'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('province.code')
                    ->label(__('Province Code'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('regency.name')
                    ->label(__('Regency / City'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('regency.code')
                    ->label(__('Regency Code'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->label(__('Area Name'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label(__('Active Status')),
                IconColumn::make('is_default')
                    ->boolean()
                    ->label(__('Default')),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    Action::make('syncSubRegions')
                        ->label(__('Sync Regencies'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->visible(fn (ProjectArea $record) => $record->province_id && ! $record->regency_id)
                        ->action(function (ProjectArea $record, WilayahSyncService $service) {
                            try {
                                if ($record->province) {
                                    $service->syncRegencies($record->province);
                                    Notification::make()
                                        ->title('Success')
                                        ->body("Regencies in {$record->province->name} have been updated.")
                                        ->success()
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
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
