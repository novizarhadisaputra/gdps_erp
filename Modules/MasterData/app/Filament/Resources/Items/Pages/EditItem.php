<?php

namespace Modules\MasterData\Filament\Resources\Items\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Resources\Items\ItemResource;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
