<?php

namespace Modules\MasterData\Filament\Resources\BillingOptions\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Resources\BillingOptions\BillingOptionResource;

class CreateBillingOption extends CreateRecord
{
    protected static string $resource = BillingOptionResource::class;
}
