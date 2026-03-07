<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtConfigs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtConfigs\JhtConfigResource;

class EditJhtConfig extends EditRecord
{
    protected static string $resource = JhtConfigResource::class;

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
