<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    public function mount(): void
    {
        parent::mount();

        $user = auth()->user();
        if ($user && ! $user->hasRole(['super_admin', 'full_access'])) {
            $this->redirect(LeadResource::getUrl('kanban'));
        }
    }

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
