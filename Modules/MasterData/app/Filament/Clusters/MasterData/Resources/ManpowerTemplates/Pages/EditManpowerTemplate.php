<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ManpowerTemplates\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ManpowerTemplateResource;

class EditManpowerTemplate extends EditRecord
{
    protected static string $resource = ManpowerTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
