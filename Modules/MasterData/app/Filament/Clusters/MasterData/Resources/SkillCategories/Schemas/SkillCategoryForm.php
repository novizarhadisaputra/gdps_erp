<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
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
            Section::make(__('Skill Category Definition'))
                ->description(__('Categorize employee skills for better resource matching and training planning.'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Category Name'))
                        ->placeholder(__('e.g. Technician, Security, Admin, Pilot'))
                        ->helperText(__('The descriptive name of the skill category.'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label(__('Category Code'))
                        ->placeholder(__('e.g. SKILL-TECH, SKILL-SEC'))
                        ->helperText(__('A unique short code identifying the skill category.'))
                        ->required()
                        ->unique(SkillCategory::class, 'code', ignoreRecord: true),
                ])->columns(2),

            Section::make(__('Status & Defaults'))
                ->description(__('Manage the availability and default status of this skill category.'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Enable or disable this category for use in employee profiles.')),
                    Toggle::make('is_default')
                        ->label(__('Default Category'))
                        ->default(false)
                        ->helperText(__('Set as the default category for new skill entries.')),
                ])->columns(2),
        ];
    }
}
