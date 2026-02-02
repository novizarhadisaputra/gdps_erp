<?php

namespace Modules\MasterData\Filament\Resources\ContactRoles\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Modules\MasterData\Filament\Resources\ContactRoles\ContactRoleResource;

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
