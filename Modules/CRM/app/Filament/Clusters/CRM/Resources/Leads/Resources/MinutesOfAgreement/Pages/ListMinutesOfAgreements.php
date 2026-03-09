<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\MinutesOfAgreementResource;

class ListMinutesOfAgreements extends ListRecords
{
    use \Filament\Resources\Pages\Concerns\InteractsWithParentRecord;

    protected static string $resource = MinutesOfAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
