<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\GeneralInformationResource;

class ListGeneralInformation extends ListRecords
{
    protected static string $resource = GeneralInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
