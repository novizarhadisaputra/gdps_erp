<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\FixedAllowanceResource;

class EditFixedAllowance extends EditRecord
{
    protected static string $resource = FixedAllowanceResource::class;

    public function getSubheading(): ?string
    {
        return 'Modify existing fixed allowance details, status, or calculation rules.';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
