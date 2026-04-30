<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\MinimumWages\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\MinimumWages\MinimumWageResource;

class EditMinimumWage extends EditRecord
{
    protected static string $resource = MinimumWageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
