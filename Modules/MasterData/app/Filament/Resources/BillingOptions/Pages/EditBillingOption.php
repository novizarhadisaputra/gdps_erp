<?php

namespace Modules\MasterData\Filament\Resources\BillingOptions\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Resources\BillingOptions\BillingOptionResource;

class EditBillingOption extends EditRecord
{
    protected static string $resource = BillingOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
