<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\SkillCategory;

class SkillCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(SkillCategory::class)
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            TextInput::make('code')
                ->required()
                ->unique(SkillCategory::class, 'code', ignoreRecord: true)
                ->placeholder('SC001'),
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Technician'),
            Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
