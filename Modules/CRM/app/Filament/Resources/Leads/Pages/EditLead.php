<?php

namespace Modules\CRM\Filament\Resources\Leads\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Resources\Leads\LeadResource;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
