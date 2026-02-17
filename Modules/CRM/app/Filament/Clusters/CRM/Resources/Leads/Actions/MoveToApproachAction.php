<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Models\Lead;

class MoveToApproachAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'moveToApproach';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Move to Approach')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Move Lead to Approach')
            ->modalDescription('Are you sure you want to move this lead to the Approach stage? This will enable Sales Plan and General Information modules.')
            ->visible(fn (Lead $record) => $record->status === LeadStatus::Lead)
            ->action(function (Lead $record) {
                $record->update([
                    'status' => LeadStatus::Approach,
                    'probability' => max($record->probability, 10),
                ]);

                Notification::make()
                    ->title('Lead Moved to Approach')
                    ->body('The lead has been successfully transitioned. You can now set up the Sales Plan.')
                    ->success()
                    ->send();

                $this->redirect(LeadResource::getUrl('sales-plans', ['record' => $record]));
            });
    }
}
