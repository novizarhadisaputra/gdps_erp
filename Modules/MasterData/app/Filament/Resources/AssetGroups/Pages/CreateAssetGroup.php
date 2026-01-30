<?php

namespace Modules\MasterData\Filament\Resources\AssetGroups\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Resources\AssetGroups\AssetGroupResource;

class CreateAssetGroup extends CreateRecord
{
    protected static string $resource = AssetGroupResource::class;
}
