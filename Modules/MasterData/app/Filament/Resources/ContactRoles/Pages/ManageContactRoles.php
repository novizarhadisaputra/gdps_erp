<?php

namespace Modules\MasterData\Filament\Resources\ContactRoles\Pages;

use Modules\MasterData\Filament\Resources\ContactRoles\ContactRoleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageContactRoles extends ManageRecords
{
    protected static string $resource = ContactRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
