<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\IndustrialSectorResource;

class EditIndustrialSector extends EditRecord
{
    protected static string $resource = IndustrialSectorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
