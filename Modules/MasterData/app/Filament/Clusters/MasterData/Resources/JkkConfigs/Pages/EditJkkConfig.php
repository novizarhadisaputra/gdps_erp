<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkConfigs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkConfigs\JkkConfigResource;

class EditJkkConfig extends EditRecord
{
    protected static string $resource = JkkConfigResource::class;

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
