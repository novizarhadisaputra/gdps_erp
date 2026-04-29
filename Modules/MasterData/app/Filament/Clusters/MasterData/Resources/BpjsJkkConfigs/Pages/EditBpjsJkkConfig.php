<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkkConfigs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkkConfigs\BpjsJkkConfigResource;

class EditBpjsJkkConfig extends EditRecord
{
    protected static string $resource = BpjsJkkConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Update Work Accident Insurance (JKK) configuration.';
    }
}
