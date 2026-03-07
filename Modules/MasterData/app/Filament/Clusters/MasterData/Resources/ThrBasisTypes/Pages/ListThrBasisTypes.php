<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ThrBasisTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ThrBasisTypes\ThrBasisTypeResource;

class ListThrBasisTypes extends ListRecords
{
    protected static string $resource = ThrBasisTypeResource::class;

    public function getSubheading(): ?string
    {
        return 'Overview of calculation bases for Religious Holiday Allowance (THR).';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
