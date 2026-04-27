<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\CreateRecord;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\WorkCompletionReportResource;
use Modules\Project\Models\Project;
use Modules\CRM\Models\Customer;

class CreateWorkCompletionReport extends CreateRecord
{
    protected static string $resource = WorkCompletionReportResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('project_id')
                ->label('Project')
                ->options(Project::query()->pluck('name', 'id'))
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(fn ($state, $set) => $set('customer_id', Project::find($state)?->customer_id)),
            Select::make('customer_id')
                ->label('Customer')
                ->options(Customer::query()->pluck('name', 'id'))
                ->searchable()
                ->required(),
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
