<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\WorkOrder;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\WorkOrder\Pages\CreateWorkOrder;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\WorkOrder\Pages\EditWorkOrder;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\WorkOrder\Pages\ViewWorkOrder;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\WorkOrder\Schemas\WorkOrderForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\WorkOrder\Tables\WorkOrdersTable;
use Modules\CRM\Models\WorkOrder;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = LeadResource::class;

    protected static ?string $parentRouteParameterName = 'lead';

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
            'create' => CreateWorkOrder::route('/create'),
            'view' => ViewWorkOrder::route('/{record}'),
            'edit' => EditWorkOrder::route('/{record}/edit'),
        ];
    }
}
