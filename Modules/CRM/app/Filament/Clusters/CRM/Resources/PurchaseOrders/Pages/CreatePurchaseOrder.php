<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\PurchaseOrders\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Modules\CRM\Filament\Clusters\CRM\Resources\PurchaseOrders\PurchaseOrderResource;
use Modules\CRM\Models\Customer;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Customer')
                    ->options(Customer::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
