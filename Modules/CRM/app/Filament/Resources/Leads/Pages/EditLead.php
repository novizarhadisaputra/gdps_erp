<?php

namespace Modules\CRM\Filament\Resources\Leads\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Resources\Leads\LeadResource;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected static ?string $navigationLabel = 'Overview';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedHome;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
