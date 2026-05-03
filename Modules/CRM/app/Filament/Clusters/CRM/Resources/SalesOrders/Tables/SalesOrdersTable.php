<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\CRM\Enums\SalesOrderStatus;
use Modules\CRM\Enums\SalesOrderType;

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
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Total Amount')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->money('IDR')
                    ),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('type')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(SalesOrderStatus::class),
                SelectFilter::make('type')
                    ->options(SalesOrderType::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn ($record) => $record->status === SalesOrderStatus::Draft),
                    DeleteAction::make()
                        ->visible(fn ($record) => $record->status === SalesOrderStatus::Draft),
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
