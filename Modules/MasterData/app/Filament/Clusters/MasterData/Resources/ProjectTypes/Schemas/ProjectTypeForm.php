<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\ProjectType;

class ProjectTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(ProjectType::class)
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Project Type Definition')
                ->description('Classify projects into different types for reporting and process management.')
                ->schema([
                    TextInput::make('name')
                        ->label('Type Name')
                        ->placeholder('e.g. Outsourcing, Consulting, Internal')
                        ->helperText('The descriptive name of the project type.')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label('Type Code')
                        ->placeholder('Auto-generated')
                        ->readOnly()
                        ->unique(ProjectType::class, 'code', ignoreRecord: true)
                        ->helperText('A unique identification code for this project type.'),
                ])->columns(2),

            Section::make('Status & Defaults')
                ->description('Manage the availability and default status of this project type.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Enable or disable this type for new projects.'),
                    Toggle::make('is_default')
                        ->label('Default Type')
                        ->default(false)
                        ->helperText('Set as the default type for new project entries.'),
                ])->columns(2),
        ];
    }
}
