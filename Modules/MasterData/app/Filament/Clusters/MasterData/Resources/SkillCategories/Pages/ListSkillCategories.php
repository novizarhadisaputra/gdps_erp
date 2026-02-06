<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Schemas\SkillCategoryForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\SkillCategoryResource;

class ListSkillCategories extends ListRecords
{
    protected static string $resource = SkillCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->schema(fn (Schema $schema) => SkillCategoryForm::configure($schema)),
        ];
    }
}
