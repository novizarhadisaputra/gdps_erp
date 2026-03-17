<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\AmendmentResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Tables\AmendmentsTable;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\SalesOrderResource;

class ManageAmendments extends ManageRelatedRecords
{
    protected static string $resource = SalesOrderResource::class;

    protected static string $relationship = 'amendments';

    protected static ?string $relatedResource = AmendmentResource::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $title = 'Amendment History';

    public function getSubheading(): ?string
    {
        return 'View and manage previous amendments of this sales order.';
    }

    public function table(Table $table): Table
    {
        return AmendmentsTable::configure($table);
    }
}
