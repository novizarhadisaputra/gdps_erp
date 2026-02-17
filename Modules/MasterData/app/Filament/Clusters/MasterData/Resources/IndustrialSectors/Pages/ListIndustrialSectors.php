<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\IndustrialSectorResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Schemas\IndustrialSectorForm;

class ListIndustrialSectors extends ListRecords
{
    protected static string $resource = IndustrialSectorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make()
                ->schema(fn (Schema $schema) => IndustrialSectorForm::configure($schema)),
        ];
    }
}
