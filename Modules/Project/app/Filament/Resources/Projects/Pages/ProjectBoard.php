<?php

namespace Modules\Project\Filament\Resources\Projects\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Modules\Project\Filament\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Resources\Projects\Schemas\ProjectForm;
use Modules\Project\Models\Project;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardResourcePage;
use Relaticle\Flowforge\Column;

class ProjectBoard extends BoardResourcePage
{
    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'Project Kanban Board';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('list')
                ->label('List View')
                ->icon('heroicon-o-table-cells')
                ->url(ProjectResource::getUrl('list')),
            CreateAction::make()->label('New project'),
        ];
    }

    public function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return static::getResource()::getEloquentQuery();
    }

    public function board(Board $board): Board
    {
        return $board
            ->query($this->getEloquentQuery())
            ->recordTitleAttribute('name')
            ->cardSchema(fn (Schema $schema) => $schema->components([
                Text::make(fn (Project $record) => $record->code)
                    ->weight(FontWeight::Bold),
            ]))
            ->columnIdentifier('status')
            ->positionIdentifier('position')
            ->columns([
                Column::make('planning')->label('Planning')->color('slate'),
                Column::make('active')->label('Active')->color('emerald'),
                Column::make('on hold')->label('On Hold')->color('amber'),
                Column::make('completed')->label('Completed')->color('blue'),
                Column::make('cancelled')->label('Cancelled')->color('rose'),
            ])
            ->cardActions([
                ViewAction::make()->form(fn (Schema $schema) => ProjectForm::configure($schema)),
                EditAction::make()->form(fn (Schema $schema) => ProjectForm::configure($schema)),
                DeleteAction::make(),
            ])
            ->cardAction('view');
    }
}
