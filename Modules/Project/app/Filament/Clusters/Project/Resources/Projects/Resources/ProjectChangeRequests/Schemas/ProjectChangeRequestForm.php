<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\Project\Enums\ProjectChangeRequestStatus;
use Modules\Project\Enums\ProjectChangeRequestType;
use Modules\Project\Models\ProjectChangeRequest;

class ProjectChangeRequestForm
{
    public static function schema(): array
    {
        return [
            Section::make('Change Request Details')
                ->description('Specify the project, the type of change and provide detailed notes regarding the adjustment.')
                ->schema([
                    TextInput::make('number')
                        ->label('PCR Number')
                        ->disabled()
                        ->placeholder('Will be generated automatically')
                        ->visible(fn (?ProjectChangeRequest $record) => $record !== null),

                    Select::make('project_id')
                        ->relationship('project', 'number')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->label('Project')
                        ->placeholder('Select a project')
                        ->helperText('The project associated with this change request.')
                        ->columnSpanFull()
                        ->hidden(fn (?ProjectChangeRequest $record) => $record && $record->project_id),

                    TextInput::make('status')
                        ->label('Status')
                        ->default('draft')
                        ->disabled()
                        ->visible(fn (?ProjectChangeRequest $record) => $record !== null),

                    Select::make('type')
                        ->label('Request Type')
                        ->options(ProjectChangeRequestType::class)
                        ->required()
                        ->live()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select the type of change')
                        ->helperText('Choose whether this change relates to Manpower or Scope of Work.')
                        ->disabled(fn (?ProjectChangeRequest $record) => $record && $record->status !== ProjectChangeRequestStatus::Draft),

                    RichEditor::make('notes')
                        ->label('Detailed Notes')
                        ->required()
                        ->placeholder('Provide a comprehensive description of the change request...')
                        ->helperText('Include specific details, reasons, and any relevant background information for this request.')
                        ->columnSpanFull()
                        ->disabled(fn (?ProjectChangeRequest $record) => $record && $record->status !== ProjectChangeRequestStatus::Draft),
                ])
                ->columns(1)
                ->columnSpanFull(),

            Section::make('Proposed Updates')
                ->description('Specify the specific values that will be updated in the Project/Project Information upon approval.')
                ->visible(fn (Get $get) => in_array($get('type'), [ProjectChangeRequestType::ScopeOfWork->value, ProjectChangeRequestType::Financial->value]))
                ->schema([
                    DatePicker::make('snapshot.end_date')
                        ->label('New End Date')
                        ->visible(fn (Get $get) => $get('type') === ProjectChangeRequestType::ScopeOfWork->value),

                    TextInput::make('snapshot.revenue_per_month')
                        ->label('New Revenue per Month')
                        ->numeric()
                        ->prefix('IDR')
                        ->visible(fn (Get $get) => $get('type') === ProjectChangeRequestType::Financial->value),

                    TextInput::make('snapshot.management_fee_per_month')
                        ->label('New Management Fee per Month')
                        ->numeric()
                        ->prefix('IDR')
                        ->visible(fn (Get $get) => $get('type') === ProjectChangeRequestType::Financial->value),
                ])->columns(2),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::schema());
    }
}
