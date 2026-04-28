<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages;

use Filament\Forms\Components\Select;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\SalesOrderResource;

class CreateSalesOrder extends CreateRecord
{
    protected static string $resource = SalesOrderResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->relationship('project', 'number')
                    ->required()
                    ->placeholder('Select project to generate SO...'),
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
