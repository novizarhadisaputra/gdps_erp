<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements\Pages;

use Filament\Actions;

use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements\CooperationAgreementResource;

class ViewCooperationAgreement extends ViewRecord
{
    protected static string $resource = CooperationAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
