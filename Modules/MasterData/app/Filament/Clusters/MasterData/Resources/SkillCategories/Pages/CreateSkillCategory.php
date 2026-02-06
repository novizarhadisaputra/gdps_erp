<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\SkillCategoryResource;

class CreateSkillCategory extends CreateRecord
{
    protected static string $resource = SkillCategoryResource::class;
}
