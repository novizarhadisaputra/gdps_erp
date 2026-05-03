<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Actions\ConvertToProjectAction;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Actions\MoveToApproachAction;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    public function getSubheading(): ?string
    {
        return 'Detailed view of the lead information, including status and associated records.';
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ActionGroup::make([
                MoveToApproachAction::make(),
                Actions\EditAction::make(),
                ConvertToProjectAction::make(),
                Actions\DeleteAction::make(),
            ])
                ->label('Actions')
                ->icon(\Filament\Support\Icons\Heroicon::OutlinedEllipsisVertical)
                ->color('primary')
                ->button(),
        ];
    }
}
