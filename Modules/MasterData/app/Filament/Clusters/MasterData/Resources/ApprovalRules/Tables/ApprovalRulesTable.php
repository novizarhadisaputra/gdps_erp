<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ApprovalRules\Tables;

use App\Models\Role;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ApprovalRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('resource_type')
                    ->formatStateUsing(fn (string $state) => class_basename($state))
                    ->label('Resource'),
                // TextColumn::make('conditions')
                //     ->label('Rules / Conditions')
                //     ->formatStateUsing(function ($state, $record) {
                //         if (empty($state)) {
                //             // Fallback to legacy base columns if conditions empty
                //             if (! $record->criteria_field) {
                //                 return 'No specific criteria';
                //             }

                //             return "{$record->criteria_field} {$record->operator} {$record->value}";
                //         }

                //         $summaries = [];
                //         foreach ((array) $state as $cond) {
                //             if (! is_array($cond)) {
                //                 continue;
                //             }

                //             $field = $cond['field'] ?? 'unknown';
                //             $op = $cond['operator'] ?? '=';
                //             $val = $cond['value'] ?? '?';

                //             // Try to resolve labels for UUIDs
                //             if ($field === 'product_cluster_id') {
                //                 if (is_array($val)) {
                //                     $names = \Modules\MasterData\Models\ProductCluster::whereIn('id', $val)->pluck('code')->toArray();
                //                     $val = implode(', ', $names);
                //                 } else {
                //                     $name = \Modules\MasterData\Models\ProductCluster::where('id', $val)->value('code');
                //                     $val = $name ?: $val;
                //                 }
                //             }

                //             $summaries[] = "[{$field} {$op} {$val}]";
                //         }

                //         return implode(' AND ', $summaries);
                //     })
                //     ->wrap()
                //     ->size('xs'),
                TextColumn::make('approver_role')
                    ->label('Approvers')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) {
                            return null;
                        }

                        $ids = (array) $state;
                        $roleNames = Role::whereIn('id', $ids)->pluck('name')->toArray();

                        return is_array($roleNames) ? implode(', ', $roleNames) : $roleNames;
                    }),
                TextColumn::make('signature_type'),
                TextColumn::make('order')
                    ->sortable(),
                ToggleColumn::make('is_active'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
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
