<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\JobPositionResource;

class ListJobPositions extends ListRecords
{
    protected static string $resource = JobPositionResource::class;

    public function getSubheading(): ?string
    {
        return 'Configure job position titles and their associated levels.';
    }

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make(),
        ];
    }
}
