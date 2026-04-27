<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Pages;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\CreateRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\InvoiceResource;
use Modules\CRM\Models\Customer;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('customer_id')
                ->label('Customer')
                ->options(Customer::query()->pluck('name', 'id'))
                ->searchable()
                ->required(),
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
