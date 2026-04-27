<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CooperationAgreement\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CooperationAgreement\CooperationAgreementResource;

class ViewCooperationAgreement extends ViewRecord
{
    use \Filament\Resources\Pages\Concerns\InteractsWithParentRecord;

    protected static string $resource = CooperationAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
