<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\PaymentTermResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Schemas\PaymentTermForm;

class ListPaymentTerms extends ListRecords
{
    protected static string $resource = PaymentTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->schema(fn (Schema $schema) => PaymentTermForm::configure($schema)),
        ];
    }
}
