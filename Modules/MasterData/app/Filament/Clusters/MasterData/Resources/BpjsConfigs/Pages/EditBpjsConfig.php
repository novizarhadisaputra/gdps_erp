<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsConfigs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsConfigs\BpjsConfigResource;

class EditBpjsConfig extends EditRecord
{
    protected static string $resource = BpjsConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
