<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers;

use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Pages\ListCustomers;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Schemas\CustomerForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Schemas\CustomerInfolist;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Tables\CustomersTable;
use Modules\MasterData\Models\Customer;

class CustomerResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = Customer::class;

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|\UnitEnum|null $navigationGroup = 'Business';

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
