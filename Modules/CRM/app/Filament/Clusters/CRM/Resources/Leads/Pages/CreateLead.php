<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;

class CreateLead extends CreateRecord
{
    protected static string $resource = LeadResource::class;
}
