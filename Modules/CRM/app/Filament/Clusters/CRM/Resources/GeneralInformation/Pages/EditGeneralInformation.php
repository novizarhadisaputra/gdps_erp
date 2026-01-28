<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\GeneralInformationResource;

class EditGeneralInformation extends EditRecord
{
    protected static string $resource = GeneralInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
