<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ApprovalRules\Tables;

use App\Models\Role;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                TextColumn::make('criteria_field'),
                TextColumn::make('operator'),
                TextColumn::make('value')
                    ->formatStateUsing(function ($state, $record) {
                        $isMonetary = in_array($record->criteria_field, ['revenue_per_month', 'net_profit', 'amount']);
                        $isPercentage = $record->criteria_field === 'margin_percentage';

                        $format = function ($val) use ($isMonetary, $isPercentage) {
                            if ($isMonetary) {
                                return 'IDR '.number_format($val, 2, ',', '.');
                            }
                            if ($isPercentage) {
                                return number_format($val, 2, ',', '.').'%';
                            }

                            return number_format($val, 2, ',', '.');
                        };

                        if ($record->operator === 'between') {
                            return $format($state).' - '.$format($record->max_value);
                        }

                        return $format($state);
                    }),
                TextColumn::make('approver_role')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        return Role::find($state)?->name;
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
