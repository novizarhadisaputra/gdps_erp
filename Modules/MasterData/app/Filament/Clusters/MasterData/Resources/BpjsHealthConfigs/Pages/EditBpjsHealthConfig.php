<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsHealthConfigs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsHealthConfigs\BpjsHealthConfigResource;

class EditBpjsHealthConfig extends EditRecord
{
    protected static string $resource = BpjsHealthConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Update Health Insurance (BPJS Kesehatan) configuration.';
    }
}
