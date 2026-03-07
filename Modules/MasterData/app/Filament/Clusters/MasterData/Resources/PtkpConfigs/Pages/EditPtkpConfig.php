<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PtkpConfigs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PtkpConfigs\PtkpConfigResource;

class EditPtkpConfig extends EditRecord
{
    protected static string $resource = PtkpConfigResource::class;

    public function getSubheading(): ?string
    {
        return 'Adjust PTKP configuration values and categories.';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
