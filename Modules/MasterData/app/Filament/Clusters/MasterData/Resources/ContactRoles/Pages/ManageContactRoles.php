<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles\ContactRoleResource;

class ManageContactRoles extends ManageRecords
{
    protected static string $resource = ContactRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make(),
        ];
    }
}
