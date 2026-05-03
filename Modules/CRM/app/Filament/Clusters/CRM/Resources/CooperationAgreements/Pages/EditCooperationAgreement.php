<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements\CooperationAgreementResource;

class EditCooperationAgreement extends EditRecord
{
    protected static string $resource = CooperationAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
