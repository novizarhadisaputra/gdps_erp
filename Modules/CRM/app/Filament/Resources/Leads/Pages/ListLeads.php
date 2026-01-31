<?php

namespace Modules\CRM\Filament\Resources\Leads\Pages;

use Modules\CRM\Filament\Resources\Leads\LeadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('kanban')
                ->label('Kanban Board')
                ->icon('heroicon-m-view-columns')
                ->url(LeadResource::getUrl('kanban')),
            Actions\CreateAction::make(),
        ];
    }
}
