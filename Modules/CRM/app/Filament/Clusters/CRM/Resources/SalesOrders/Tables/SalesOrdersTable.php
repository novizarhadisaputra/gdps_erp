<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\CRM\Enums\SalesOrderStatus;
use Modules\CRM\Models\SalesOrder;

class SalesOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('SO Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.number')
                    ->label('Project Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->relationship('customer', 'name'),
                SelectFilter::make('status')
                    ->options(SalesOrderStatus::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn (SalesOrder $record) => $record->status === SalesOrderStatus::Draft),
                    DeleteAction::make()
                        ->visible(fn (SalesOrder $record) => $record->status === SalesOrderStatus::Draft),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
