<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PartnerFeeTypes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PartnerFeeTypes\PartnerFeeTypeResource;

class EditPartnerFeeType extends EditRecord
{
    protected static string $resource = PartnerFeeTypeResource::class;

    public function getSubheading(): ?string
    {
        return 'Adjust partner fee type configuration and active status.';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
