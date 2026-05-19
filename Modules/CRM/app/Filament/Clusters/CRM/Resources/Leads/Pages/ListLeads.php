<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
            Actions\Action::make(__('kanban'))
                ->label(__('Kanban Board'))
                ->icon(Heroicon::ViewColumns)
                ->url(LeadResource::getUrl('index')),
            Actions\CreateAction::make()
                ->schema(fn (Schema $schema) => LeadForm::configure($schema)),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->icon(Heroicon::OutlinedListBullet),
            'active' => Tab::make()
                ->icon(Heroicon::OutlinedFunnel)
                ->modifyQueryUsing(fn ($query) => $query->withoutTrashed()),
            'archived' => Tab::make()
                ->label(__('Trash / Archived'))
                ->icon(Heroicon::OutlinedTrash)
                ->modifyQueryUsing(fn ($query) => $query->onlyTrashed()),
        ];
    }
}
