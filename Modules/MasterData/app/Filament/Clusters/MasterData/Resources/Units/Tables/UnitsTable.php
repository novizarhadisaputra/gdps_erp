<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\MasterData\Models\Unit;
use Modules\MasterData\Services\UnitService;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->records(function (int $page, int $recordsPerPage, ?string $tableSearch): LengthAwarePaginator {
                /** @var UnitService $service */
                $service = app(UnitService::class);

                $allUnits = $service->getAllUnits();

                if ($tableSearch) {
                    $allUnits = $allUnits->filter(function (Unit $unit) use ($tableSearch) {
                        return str_contains(strtolower($unit->name ?? ''), strtolower($tableSearch)) ||
                               str_contains(strtolower($unit->code ?? ''), strtolower($tableSearch));
                    });
                }

                $items = $allUnits->forPage($page, $recordsPerPage)->values();

                return new LengthAwarePaginator(
                    $items,
                    $allUnits->count(),
                    $recordsPerPage,
                    $page,
                    ['path' => request()->url(), 'query' => request()->query()]
                );
            })
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('superior_unit')
                    ->label('Superior Unit')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
