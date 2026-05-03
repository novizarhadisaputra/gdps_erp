<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements\CooperationAgreementResource;

class ListCooperationAgreements extends ListRecords
{
    protected static string $resource = CooperationAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
