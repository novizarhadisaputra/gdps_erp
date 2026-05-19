<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\PurchaseOrder\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\PurchaseOrder\PurchaseOrderResource;
use Modules\CRM\Models\PurchaseOrder;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label(__('Number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('proposal.number')
                    ->label(__('Proposal'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->money('IDR')
                    ->sortable()
                    ->alignRight(),
                TextColumn::make('order_date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn (PurchaseOrder $record) => PurchaseOrderResource::getUrl('view', ['lead' => $record->lead_id, 'record' => $record->id])),
                    EditAction::make()
                        ->url(fn (PurchaseOrder $record) => PurchaseOrderResource::getUrl('edit', ['lead' => $record->lead_id, 'record' => $record->id])),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
