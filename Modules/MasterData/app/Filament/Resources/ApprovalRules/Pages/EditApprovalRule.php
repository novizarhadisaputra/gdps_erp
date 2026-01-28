<?php

namespace Modules\MasterData\Filament\Resources\ApprovalRules\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Resources\ApprovalRules\ApprovalRuleResource;

class EditApprovalRule extends EditRecord
{
    protected static string $resource = ApprovalRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
