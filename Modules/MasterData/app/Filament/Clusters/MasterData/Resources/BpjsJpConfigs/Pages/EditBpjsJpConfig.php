<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJpConfigs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJpConfigs\BpjsJpConfigResource;

class EditBpjsJpConfig extends EditRecord
{
    protected static string $resource = BpjsJpConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Update Pension Security (JP) configuration.';
    }
}
