<?php

namespace Modules\MasterData\Filament\Resources\PaymentTerms\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Resources\PaymentTerms\PaymentTermResource;

class CreatePaymentTerm extends CreateRecord
{
    protected static string $resource = PaymentTermResource::class;
}
