<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JpConfigs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JpConfigs\JpConfigResource;

class EditJpConfig extends EditRecord
{
    protected static string $resource = JpConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Update Pension Security (JP) configuration.';
    }
}
