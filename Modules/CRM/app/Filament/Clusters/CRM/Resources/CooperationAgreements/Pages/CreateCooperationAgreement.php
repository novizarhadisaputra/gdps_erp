<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements\CooperationAgreementResource;
use Modules\CRM\Models\Customer;

class CreateCooperationAgreement extends CreateRecord
{
    protected static string $resource = CooperationAgreementResource::class;

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
