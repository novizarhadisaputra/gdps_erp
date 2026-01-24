<?php

namespace Modules\MasterData\Filament\Resources\Employees\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Resources\Employees\EmployeeResource;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;
}
