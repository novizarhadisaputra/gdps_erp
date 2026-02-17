<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ApprovalRules\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ApprovalRules\ApprovalRuleResource;

class ListApprovalRules extends ListRecords
{
    protected static string $resource = ApprovalRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make(),
        ];
    }
}
