<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders;

use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages\{CreateSalesOrder, EditSalesOrder, ListSalesOrders, ManageAmendments, SendSalesOrder, ViewSalesOrder};
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Pages\ViewAmendment;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Schemas\{SalesOrderForm, SalesOrderInfolist};
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
        return SalesOrderInfolist::configure($schema);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewSalesOrder::class,
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
            'view' => ViewSalesOrder::route('/{record}'),
            'edit' => EditSalesOrder::route('/{record}/edit'),
            'send' => SendSalesOrder::route('/{record}/send'),
            'amendments' => ManageAmendments::route('/{record}/amendments'),
            'view-amendment' => ViewAmendment::route('/{record}/amendments/{relatedRecord}'),
        ];
    }
}
