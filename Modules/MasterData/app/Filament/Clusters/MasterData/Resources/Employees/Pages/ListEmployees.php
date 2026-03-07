<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees\EmployeeResource;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    public function getSubheading(): ?string
    {
        return 'Manage employee records, personal details, and employment history.';
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
