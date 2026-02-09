<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Schemas\LeadForm;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('kanban')
                ->label('Kanban Board')
                ->icon('heroicon-m-view-columns')
                ->url(LeadResource::getUrl('index')),
            Actions\CreateAction::make()
                ->form(fn (Schema $schema) => LeadForm::configure($schema)),
        ];
    }
}
