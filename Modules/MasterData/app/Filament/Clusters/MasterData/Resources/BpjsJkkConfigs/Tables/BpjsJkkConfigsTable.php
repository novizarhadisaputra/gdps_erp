<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkkConfigs\Tables;

use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BpjsJkkConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->description('Work Accident Insurance (JKK) configurations for participants. Manages contribution percentages based on occupational risk levels.')
            ->columns([
                TextColumn::make('name')
                    ->label('Config Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof BackedEnum ? $state->value : $state) {
                        'ppu' => 'primary',
                        'pbpu' => 'warning',
                        'pbi' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                TextColumn::make('employer_rate')
                    ->label('Employer Rate (%)')
                    ->numeric(4)
                    ->suffix('%'),
                TextColumn::make('employee_rate')
                    ->label('Employee Rate (%)')
                    ->numeric(4)
                    ->suffix('%'),
                IconColumn::make('is_active')
                    ->label('Active Status')
                    ->boolean(),
                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->icon(fn ($state) => $state ? 'heroicon-o-star' : null)
                    ->color('warning'),
            ])
            ->filters([
                SelectFilter::make('employee_type')
                    ->label('Membership Type')
                    ->options([
                        'ppu' => 'PPU',
                        'pbpu' => 'PBPU',
                        'pbi' => 'PBI',
                    ]),
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
                ActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
