<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Enums\MoAStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\MinutesOfAgreementResource;
use Modules\CRM\Models\MinutesOfAgreement;

class ViewMinutesOfAgreement extends ViewRecord
{
    use \Filament\Resources\Pages\Concerns\InteractsWithParentRecord;

    protected static string $resource = MinutesOfAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sign')
                ->label('Sign MoA')
                ->icon('heroicon-o-pencil-square')
                ->color('success')
                ->visible(fn (MinutesOfAgreement $record) => $record->status !== MoAStatus::Approved)
                ->requireSignature(
                    documentName: fn (MinutesOfAgreement $record) => "Minutes of Agreement {$record->moa_number}",
                    propertyName: 'status',
                    targetValue: MoAStatus::Approved
                ),
            Action::make('convertToContract')
                ->label('Convert to Contract')
                ->icon('heroicon-o-document-duplicate')
                ->color('primary')
                ->visible(fn (MinutesOfAgreement $record) => $record->status === MoAStatus::Approved && ! $record->proposal?->contracts()->exists())
                ->requiresConfirmation()
                ->action(function (MinutesOfAgreement $record) {
                    $contract = \Modules\CRM\Models\Contract::create([
                        'customer_id' => $record->customer_id,
                        'lead_id' => $record->lead_id,
                        'proposal_id' => $record->proposal_id,
                        'status' => \Modules\CRM\Enums\ContractStatus::Draft,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('MoA Converted to Contract')
                        ->success()
                        ->send();

                    $this->redirect(\Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\ContractResource::getUrl('edit', ['record' => $contract->id, 'lead' => $record->lead_id]));
                }),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
