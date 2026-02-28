<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\ManpowerTemplateResource;
use Modules\CRM\Traits\CanImportAi;

class ListManpowerTemplates extends ListRecords
{
    use CanImportAi;

    protected static string $resource = ManpowerTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            $this->getImportManpowerAiAction(),
            CreateAction::make(),
        ];
    }
}
