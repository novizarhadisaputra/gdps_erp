<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\DailyReports\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Project\Enums\DailyReportStatus;

class DailyReportForm
{
    public static function schema(): array
    {
        return [
            Section::make('General Information')
                ->schema([
                    DatePicker::make('date')
                        ->required()
                        ->default(now()),
                    Select::make('reported_by_id')
                        ->label('Reported By')
                        ->relationship('reportedBy', 'id')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->memberable?->name ?? 'Unknown')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('task_id')
                        ->label('Related Task')
                        ->relationship('task', 'name', fn ($query, $get) => $query->where('project_id', $get('project_id')))
                        ->searchable()
                        ->placeholder('Optional: select a task'),
                    Select::make('status')
                        ->options(DailyReportStatus::class)
                        ->required()
                        ->default(DailyReportStatus::Draft),
                ])->columns(3),

            Section::make('Report Content')
                ->schema([
                    Textarea::make('content')
                        ->label('Activities / Progress Details')
                        ->required()
                        ->rows(5)
                        ->columnSpanFull(),
                    TextInput::make('weather')
                        ->placeholder('e.g. Sunny, Rainy'),
                    Textarea::make('site_condition')
                        ->placeholder('e.g. Site is clear, Material arrived')
                        ->rows(2),
                ])->columns(2),

            Section::make('Documentation')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('site_photos')
                        ->collection('site_photos')
                        ->multiple()
                        ->reorderable()
                        ->image()
                        ->visibility('private')
                        ->columnSpanFull(),
                ]),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::schema());
    }
}
