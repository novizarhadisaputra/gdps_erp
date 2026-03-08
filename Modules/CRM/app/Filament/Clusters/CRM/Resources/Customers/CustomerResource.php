<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Customers;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Resources\Customers\Pages\ListCustomers;
use Modules\CRM\Filament\Clusters\CRM\Resources\Customers\Schemas\CustomerForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Customers\Schemas\CustomerInfolist;
use Modules\CRM\Filament\Clusters\CRM\Resources\Customers\Tables\CustomersTable;
use Modules\CRM\Models\Customer;

class CustomerResource extends Resource
{
    protected static ?string $cluster = CRMCluster::class;

    protected static ?string $model = Customer::class;

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|\UnitEnum|null $navigationGroup = 'Partners & Relations';

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
        ];
    }
}
