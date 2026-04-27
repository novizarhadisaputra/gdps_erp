<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\WorkOrder\WorkOrderResource;

class ManageWorkOrders extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'workOrders';

    protected static ?string $relatedResource = WorkOrderResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedWrench;

    public function getSubheading(): ?string
    {
        return 'Manage Work Orders (SPK) for this lead.';
    }

    protected static ?string $title = 'Work Orders';

    public function form(Schema $schema): Schema
    {
        return WorkOrderResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return WorkOrderResource::table($table)
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->url(fn () => WorkOrderResource::getUrl('create', ['lead' => $this->getOwnerRecord()->id])),
            ]);
    }
}
