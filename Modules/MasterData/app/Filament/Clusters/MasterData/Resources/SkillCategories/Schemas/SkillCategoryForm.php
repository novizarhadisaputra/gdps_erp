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
            Section::make('Skill Category Definition')
                ->description('Categorize employee skills for better resource matching and training planning.')
                ->schema([
                    TextInput::make('name')
                        ->label('Category Name')
                        ->placeholder('e.g. Technician, Security, Admin, Pilot')
                        ->helperText('The descriptive name of the skill category.')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label('Category Code')
                        ->placeholder('e.g. SKILL-TECH, SKILL-SEC')
                        ->helperText('A unique short code identifying the skill category.')
                        ->required()
                        ->unique(SkillCategory::class, 'code', ignoreRecord: true),
                ])->columns(2),

            Section::make('Status & Defaults')
                ->description('Manage the availability and default status of this skill category.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Enable or disable this category for use in employee profiles.'),
                    Toggle::make('is_default')
                        ->label('Default Category')
                        ->default(false)
                        ->helperText('Set as the default category for new skill entries.'),
                ])->columns(2),
        ];
    }
}
