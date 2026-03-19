<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions;
use Filament\Support\Icons\Heroicon;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Schemas\LeadForm;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    public function getSubheading(): ?string
    {
        return 'Manage potential business opportunities and customer inquiries.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('kanban')
                ->label('Kanban Board')
                ->icon(Heroicon::ViewColumns)
                ->url(LeadResource::getUrl('index')),
            Actions\CreateAction::make()
                ->form(fn (Schema $schema) => LeadForm::configure($schema)),
        ];
    }
}
