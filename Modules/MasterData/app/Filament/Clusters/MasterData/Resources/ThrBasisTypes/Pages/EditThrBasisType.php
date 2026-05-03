<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ThrBasisTypes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ThrBasisTypes\ThrBasisTypeResource;

class EditThrBasisType extends EditRecord
{
    protected static string $resource = ThrBasisTypeResource::class;

    public function getSubheading(): ?string
    {
        return 'Adjust THR basis type parameters.';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
