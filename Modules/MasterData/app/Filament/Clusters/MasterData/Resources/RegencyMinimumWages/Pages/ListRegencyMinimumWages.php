<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages\RegencyMinimumWageResource;

class ListRegencyMinimumWages extends ListRecords
{
    protected static string $resource = RegencyMinimumWageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary')
                ->processCollectionUsing(function (\Illuminate\Support\Collection $collection) {
                    $collection->each(function ($row) {
                        $projectAreaName = $row['area_kerja'] ?? null;
                        $projectAreaId = null;

                        if ($projectAreaName) {
                            $projectAreaId = \Modules\MasterData\Models\ProjectArea::where('name', 'like', "%{$projectAreaName}%")
                                ->first()?->id;
                        }

                        if ($projectAreaId) {
                            \Modules\MasterData\Models\RegencyMinimumWage::updateOrCreate(
                                [
                                    'project_area_id' => $projectAreaId,
                                    'year' => $row['tahun'] ?? date('Y'),
                                ],
                                [
                                    'province' => $row['provinsi'],
                                    'type' => $row['tipe'],
                                    'amount' => $this->parseAmount($row['nominal_umk']),
                                    'is_active' => true,
                                ]
                            );
                        }
                    });

                    return $collection;
                }),
            CreateAction::make(),
        ];
    }

    protected function parseAmount($amount): float
    {
        if (is_numeric($amount)) {
            return (float) $amount;
        }

        $clean = str_replace(['IDR', '.', ' ', ','], ['', '', '', '.'], $amount);

        return (float) $clean;
    }
}
