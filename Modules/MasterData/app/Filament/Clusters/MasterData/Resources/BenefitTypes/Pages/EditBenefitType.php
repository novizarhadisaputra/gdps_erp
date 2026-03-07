<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\BenefitTypeResource;

class EditBenefitType extends EditRecord
{
    protected static string $resource = BenefitTypeResource::class;

    public function getSubheading(): ?string
    {
        return 'Update benefit type details and status.';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
