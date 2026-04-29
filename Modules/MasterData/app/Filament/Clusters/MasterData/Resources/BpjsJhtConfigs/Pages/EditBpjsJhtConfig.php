<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJhtConfigs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJhtConfigs\BpjsJhtConfigResource;

class EditBpjsJhtConfig extends EditRecord
{
    protected static string $resource = BpjsJhtConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Update Old Age Security (JHT) configuration.';
    }
}
