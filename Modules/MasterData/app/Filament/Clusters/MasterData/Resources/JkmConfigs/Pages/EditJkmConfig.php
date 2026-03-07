<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmConfigs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmConfigs\JkmConfigResource;

class EditJkmConfig extends EditRecord
{
    protected static string $resource = JkmConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Update Life Insurance (JKM) configuration.';
    }
}
