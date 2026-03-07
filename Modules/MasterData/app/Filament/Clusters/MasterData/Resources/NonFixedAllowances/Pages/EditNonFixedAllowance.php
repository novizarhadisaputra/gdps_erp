<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances\NonFixedAllowanceResource;

class EditNonFixedAllowance extends EditRecord
{
    protected static string $resource = NonFixedAllowanceResource::class;

    public function getSubheading(): ?string
    {
        return 'Update non-fixed allowance parameters and taxable status.';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
