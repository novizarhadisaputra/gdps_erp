<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Vendors\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Vendors\VendorResource;

class ListVendors extends ListRecords
{
    protected static string $resource = VendorResource::class;

    public function getSubheading(): ?string
    {
        return 'Maintain supplier and partner organization profiles.';
    }

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make(),
        ];
    }
}
