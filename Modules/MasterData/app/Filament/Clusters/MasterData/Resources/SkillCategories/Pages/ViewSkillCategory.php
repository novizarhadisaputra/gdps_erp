<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\SkillCategoryResource;

class ViewSkillCategory extends ViewRecord
{
    protected static string $resource = SkillCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
