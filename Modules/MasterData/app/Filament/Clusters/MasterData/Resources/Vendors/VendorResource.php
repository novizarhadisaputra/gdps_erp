<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Vendors;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Vendors\Pages\ListVendors;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Vendors\Schemas\VendorForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Vendors\Tables\VendorsTable;
use Modules\MasterData\Models\Vendor;

class VendorResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = Vendor::class;

    protected static ?int $navigationSort = 4; // Adjusted sort order

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string|\UnitEnum|null $navigationGroup = 'Partners & Relations';

    public static function form(Schema $schema): Schema
    {
        return VendorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VendorsTable::configure($table);
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
            'index' => ListVendors::route('/'),
        ];
    }
}
