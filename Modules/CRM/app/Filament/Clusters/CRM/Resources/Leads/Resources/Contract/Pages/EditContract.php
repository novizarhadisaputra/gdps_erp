<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\ContractResource;

class EditContract extends EditRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
