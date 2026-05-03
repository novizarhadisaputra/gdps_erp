<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes\RevenueTypeResource;

class EditRevenueType extends EditRecord
{
    protected static string $resource = RevenueTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
