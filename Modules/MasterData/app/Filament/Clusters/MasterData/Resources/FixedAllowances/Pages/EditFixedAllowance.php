<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\FixedAllowanceResource;

class EditFixedAllowance extends EditRecord
{
    protected static string $resource = FixedAllowanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
