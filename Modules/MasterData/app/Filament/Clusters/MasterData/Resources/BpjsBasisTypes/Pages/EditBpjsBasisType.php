<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsBasisTypes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsBasisTypes\BpjsBasisTypeResource;

class EditBpjsBasisType extends EditRecord
{
    protected static string $resource = BpjsBasisTypeResource::class;

    public function getSubheading(): ?string
    {
        return 'Modify BPJS basis type configuration.';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
