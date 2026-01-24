<?php

namespace Modules\MasterData\Filament\Resources\WorkSchemes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Resources\WorkSchemes\WorkSchemeResource;

class EditWorkScheme extends EditRecord
{
    protected static string $resource = WorkSchemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
