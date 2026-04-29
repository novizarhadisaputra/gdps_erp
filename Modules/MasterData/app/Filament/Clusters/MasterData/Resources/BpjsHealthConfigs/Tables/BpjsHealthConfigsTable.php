<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsHealthConfigs\Tables;

use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BpjsHealthConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->description('Health Insurance (BPJS Kesehatan) configurations. Manages calculations for PPU, PBPU, and PBI.')
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
                EditAction::make(),
            ])
            ->toolbarActions([
                ActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
