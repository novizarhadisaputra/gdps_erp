<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\BankAccountResource;

class ListBankAccounts extends ListRecords
{
    protected static string $resource = BankAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
