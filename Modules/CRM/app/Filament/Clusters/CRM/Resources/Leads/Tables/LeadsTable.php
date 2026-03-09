<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Actions\MoveToApproachAction;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Schemas\LeadInfolist;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('estimated_amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('items_count')
                    ->counts('proposals')
                    ->label('Proposals'),
            ])
            ->recordActions([
                MoveToApproachAction::make(),
                ViewAction::make()
                    ->schema(fn ($schema) => LeadInfolist::configure($schema)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
