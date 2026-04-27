<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CooperationAgreement\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CooperationAgreement\CooperationAgreementResource;

class EditCooperationAgreement extends EditRecord
{
    use \Filament\Resources\Pages\Concerns\InteractsWithParentRecord;

    protected static string $resource = CooperationAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
