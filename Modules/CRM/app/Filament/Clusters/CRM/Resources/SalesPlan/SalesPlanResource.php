<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesPlan;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesPlan\Pages\ListSalesPlans;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesPlan\Schemas\SalesPlanForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesPlan\Tables\SalesPlanTable;
use Modules\CRM\Models\SalesPlan;

class SalesPlanResource extends Resource
{
    protected static ?string $model = SalesPlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 2;

    protected static ?string $cluster = CRMCluster::class;

    public static function form(Schema $schema): Schema
    {
        return SalesPlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesPlanTable::configure($table);
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
            'index' => ListSalesPlans::route('/'),
        ];
    }
}
