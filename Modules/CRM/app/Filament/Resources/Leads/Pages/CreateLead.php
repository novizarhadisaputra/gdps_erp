<?php

namespace Modules\CRM\Filament\Resources\Leads\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Resources\Leads\LeadResource;

class CreateLead extends CreateRecord
{
    protected static string $resource = LeadResource::class;
}
