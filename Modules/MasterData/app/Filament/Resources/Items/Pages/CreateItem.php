<?php

namespace Modules\MasterData\Filament\Resources\Items\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Resources\Items\ItemResource;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;
}
