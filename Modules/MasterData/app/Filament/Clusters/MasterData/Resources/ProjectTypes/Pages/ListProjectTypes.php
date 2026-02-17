<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectTypes\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectTypes\ProjectTypeResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectTypes\Schemas\ProjectTypeForm;

class ListProjectTypes extends ListRecords
{
    protected static string $resource = ProjectTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make()
                ->schema(fn (Schema $schema) => ProjectTypeForm::configure($schema)),
        ];
    }
}
