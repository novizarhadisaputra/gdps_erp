<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\GeneralInformationResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas\GeneralInformationForm;

class ListGeneralInformation extends ListRecords
{
    protected static string $resource = GeneralInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->schema(fn (Schema $schema) => GeneralInformationForm::configure($schema)),
        ];
    }
}
