<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\SkillCategoryResource;

class EditSkillCategory extends EditRecord
{
    protected static string $resource = SkillCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
