<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkmConfigs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkmConfigs\BpjsJkmConfigResource;

class EditBpjsJkmConfig extends EditRecord
{
    protected static string $resource = BpjsJkmConfigResource::class;

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
