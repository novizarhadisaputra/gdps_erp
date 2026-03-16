<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\Pages\CreateSalesPlan;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\Pages\EditSalesPlan;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\Pages\ListSalesPlans;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\Pages\ViewSalesPlan;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\Schemas\SalesPlanForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\Schemas\SalesPlanInfolist;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\Tables\SalesPlanTable;
use Modules\CRM\Models\SalesPlan;

class SalesPlanResource extends Resource
{
    protected static ?string $model = SalesPlan::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = LeadResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    public static function form(Schema $schema): Schema
    {
        return SalesPlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesPlanTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SalesPlanInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesPlans::route('/'),
            'create' => CreateSalesPlan::route('/create'),
            'view' => ViewSalesPlan::route('/{record}'),
            'edit' => EditSalesPlan::route('/{record}/edit'),
        ];
    }
}
