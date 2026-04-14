<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders;

use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages\CreateSalesOrder;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages\EditSalesOrder;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages\ListSalesOrders;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages\ManageAmendments;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Pages\ViewAmendment;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Schemas\SalesOrderForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Tables\SalesOrdersTable;
use Modules\CRM\Models\SalesOrder;

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = CRMCluster::class;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return SalesOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesOrdersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return \Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Schemas\SalesOrderInfolist::configure($schema);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            \Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages\ViewSalesOrder::class,
            EditSalesOrder::class,
            ManageAmendments::class,
        ]);
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
            'index' => ListSalesOrders::route('/'),
            'create' => CreateSalesOrder::route('/create'),
            'view' => \Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages\ViewSalesOrder::route('/{record}'),
            'edit' => EditSalesOrder::route('/{record}/edit'),
            'amendments' => ManageAmendments::route('/{record}/amendments'),
            'view-amendment' => ViewAmendment::route('/{record}/amendments/{relatedRecord}'),
        ];
    }
}
