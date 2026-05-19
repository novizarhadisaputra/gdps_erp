<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\WorkOrders;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Resources\WorkOrders\Pages\CreateWorkOrder;
use Modules\CRM\Filament\Clusters\CRM\Resources\WorkOrders\Pages\EditWorkOrder;
use Modules\CRM\Filament\Clusters\CRM\Resources\WorkOrders\Pages\ListWorkOrders;
use Modules\CRM\Filament\Clusters\CRM\Resources\WorkOrders\Pages\ViewWorkOrder;
use Modules\CRM\Filament\Clusters\CRM\Resources\WorkOrders\Schemas\WorkOrderForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\WorkOrders\Tables\WorkOrdersTable;
use Modules\CRM\Models\WorkOrder;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $cluster = CRMCluster::class;

    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return __('Work Order');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Work Orders');
    }

    public static function getNavigationLabel(): string
    {
        return __('Work Orders');
    }

    public static function form(Schema $schema): Schema
    {
        return WorkOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkOrdersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkOrders::route('/'),
            'create' => CreateWorkOrder::route('/create'),
            'view' => ViewWorkOrder::route('/{record}'),
            'edit' => EditWorkOrder::route('/{record}/edit'),
        ];
    }
}
