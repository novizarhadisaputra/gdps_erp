<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\ProjectAreaResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;

class ListProjectAreas extends ListRecords
{
    protected static string $resource = ProjectAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make()
                ->schema(fn (Schema $schema) => ProjectAreaForm::configure($schema)),
        ];
    }
}
