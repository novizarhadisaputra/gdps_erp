<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtTypes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtTypes\JhtTypeResource;

class EditJhtType extends EditRecord
{
    protected static string $resource = JhtTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
