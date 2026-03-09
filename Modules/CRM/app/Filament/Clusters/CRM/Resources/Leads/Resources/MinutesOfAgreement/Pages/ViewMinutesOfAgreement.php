<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Enums\MoAStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\MinutesOfAgreementResource;
use Modules\CRM\Models\MinutesOfAgreement;
use Modules\MasterData\Services\SignatureService;

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
            EditAction::make(),
        ];
    }
}
