<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectTasks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Project\Enums\TaskPriority;
use Modules\Project\Enums\TaskStatus;

class ProjectTaskForm
{
    public static function schema(): array
    {
        return [
            Section::make('Task Information')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                    Select::make('parent_id')
                        ->label('Parent Task (WBS)')
                        ->relationship('parent', 'name', fn ($query, $get) => $query->where('project_id', $get('project_id')))
                        ->searchable()
                        ->placeholder('Select parent task if any'),
                    Select::make('assigned_member_id')
                        ->label('Assigned To')
                        ->relationship('assignedMember', 'id')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->memberable?->name ?? 'Unknown')
                        ->searchable()
                        ->preload()
                        ->placeholder('Select member'),
                ])->columns(2),

            Section::make('Planning & Progress')
                ->schema([
                    Select::make('status')
                        ->options(TaskStatus::class)
                        ->required()
                        ->native(false)
                        ->default(TaskStatus::Todo),
                    Select::make('priority')
                        ->options(TaskPriority::class)
                        ->required()
                        ->native(false)
                        ->default(TaskPriority::Medium),
                    DatePicker::make('start_date'),
                    DatePicker::make('end_date'),
                    TextInput::make('progress_percentage')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%'),
                ])->columns(2),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::schema());
    }
}
