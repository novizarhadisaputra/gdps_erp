<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccountMappingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mappable_type')
                    ->label('Entity Type')
                    ->state(fn ($record) => class_basename($record->mappable_type))
                    ->badge(),
                TextColumn::make('mappable.name')
                    ->label('Entity Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Mapping Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'accrual' => 'warning',
                        'revenue' => 'success',
                        'receivable' => 'primary',
                        'unbilled_receivable' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('revenueType.name')
                    ->label('Revenue Type')
                    ->placeholder('All Types')
                    ->sortable(),
                TextColumn::make('revenueSegment.name')
                    ->label('Segment')
                    ->placeholder('All Segments')
                    ->sortable(),
                TextColumn::make('chartOfAccount.name')
                    ->label('GL Account')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
            ])
            ->filters([
                SelectFilter::make('mappable_type')
                    ->label('Entity Type')
                    ->options([
                        \Modules\MasterData\Models\ProjectArea::class => 'Project Area',
                        \Modules\CRM\Models\Customer::class => 'Customer',
                    ]),
                SelectFilter::make('type')
                    ->label('Mapping Type')
                    ->options([
                        'accrual' => 'Accrual',
                        'revenue' => 'Revenue',
                        'receivable' => 'Receivable',
                        'unbilled_receivable' => 'Unbilled Receivable',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
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
