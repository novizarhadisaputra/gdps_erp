<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\CRM\Models\Customer;
use Modules\Finance\Models\AccountMapping;
use Modules\MasterData\Models\ProjectArea;

class AccountMappingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer')
                    ->label('Customer')
                    ->state(function (AccountMapping $record) {
                        if ($record->mappable instanceof Customer) {
                            return $record->mappable->name;
                        }
                        if ($record->mappable instanceof ProjectArea) {
                            return $record->mappable->getCustomer()?->name ?? 'Unknown';
                        }

                        return '-';
                    }),
                TextColumn::make('mappable.name')
                    ->label('Project Area')
                    ->state(function (AccountMapping $record) {
                        if ($record->mappable instanceof ProjectArea) {
                            return $record->mappable->name;
                        }

                        return '(Customer Level)';
                    })
                    ->color(fn ($state) => $state === '(Customer Level)' ? 'gray' : 'primary')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Mapping Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'accrual' => 'warning',
                        'revenue' => 'success',
                        'receivable' => 'primary',
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
                    ->description(fn (AccountMapping $record) => $record->chartOfAccount?->code)
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('note')
                    ->label('Note')
                    ->searchable()
                    ->toggleable()
                    ->limit(30),
            ])
            ->filters([
                SelectFilter::make('mappable_type')
                    ->label('Entity Type')
                    ->options([
                        ProjectArea::class => 'Project Area',
                        Customer::class => 'Customer',
                    ]),
                SelectFilter::make('type')
                    ->label('Mapping Type')
                    ->options([
                        'accrual' => 'Accrual',
                        'revenue' => 'Revenue',
                        'receivable' => 'Receivable',
                    ]),
                SelectFilter::make('customer_id')
                    ->label('Filter by Customer')
                    ->options(Customer::pluck('name', 'id'))
                    ->searchable()
                    ->query(function ($query, array $data) {
                        if (! $data['value']) {
                            return;
                        }

                        $query->where(function ($q) use ($data) {
                            $q->where(function ($sub) use ($data) {
                                $sub->where('mappable_type', Customer::class)
                                    ->where('mappable_id', $data['value']);
                            })->orWhere(function ($sub) use ($data) {
                                $sub->where('mappable_type', ProjectArea::class)
                                    ->whereIn('mappable_id', function ($inner) use ($data) {
                                        $inner->select('project_area_id')
                                            ->from('crm.customer_project_area')
                                            ->where('customer_id', $data['value']);
                                    });
                            });
                        });
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(50);
    }
}
