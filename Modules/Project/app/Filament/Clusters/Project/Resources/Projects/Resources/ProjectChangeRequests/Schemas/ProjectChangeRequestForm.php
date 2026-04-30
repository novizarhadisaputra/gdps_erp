<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Project\Enums\ProjectChangeRequestType;

class ProjectChangeRequestForm
{
    public static function schema(): array
    {
        return [
            Section::make('Change Request Details')
                ->description('Specify the type of change and provide detailed notes regarding the adjustment.')
                ->schema([
                    Select::make('type')
                        ->label('Request Type')
                        ->options(ProjectChangeRequestType::class)
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select the type of change')
                        ->helperText('Choose whether this change relates to Manpower or Scope of Work.'),

                    RichEditor::make('notes')
                        ->label('Detailed Notes')
                        ->required()
                        ->placeholder('Provide a comprehensive description of the change request...')
                        ->helperText('Include specific details, reasons, and any relevant background information for this request.')
                        ->columnSpanFull(),
                ])
                ->columns(1)
                ->columnSpanFull(),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::schema());
    }
}
