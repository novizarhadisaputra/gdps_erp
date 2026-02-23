<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\GeneralInformationResource;

class ListGeneralInformation extends ListRecords
{
    protected static string $resource = GeneralInformationResource::class;
}
