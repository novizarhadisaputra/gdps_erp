<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\HealthConfigs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\HealthConfigs\HealthConfigResource;

class EditHealthConfig extends EditRecord
{
    protected static string $resource = HealthConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Update Health Insurance (BPJS Kesehatan) configuration.';
    }
}
