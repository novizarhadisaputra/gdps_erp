<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use Modules\CRM\Enums\SalesOrderAmendmentStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\AmendmentResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Tables\AmendmentsTable;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\SalesOrderResource;

class ManageAmendments extends ManageRelatedRecords
{
    protected static string $resource = SalesOrderResource::class;

    protected static string $relationship = 'amendments';

    protected static ?string $relatedResource = AmendmentResource::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $title = 'Amendment History';

    public function getSubheading(): ?string
    {
        return 'View and manage previous amendments of this sales order.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make(__('proposeAmendment'))
                ->label(__('Propose New Amendment'))
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading(__('Propose New Sales Order Amendment'))
                ->modalDescription(__('This will create a draft amendment using the current Sales Order state as the baseline. You can then edit the revised details manually.'))
                ->action(function () {
                    $so = $this->getOwnerRecord();

                    // 1. Prepare Snapshots
                    $beforeSnapshot = $so->content_config;
                    // Initialize After snapshot with same data so user only edits what's different
                    $afterSnapshot = $beforeSnapshot;

                    // 2. Create Draft (Observer will handle amendment_number, sequence_number and year)
                    $amendment = $so->amendments()->create([
                        'amendment_date' => now(),
                        'reason' => 'Manual amendment proposal',
                        'status' => SalesOrderAmendmentStatus::Draft,
                        'before_snapshot' => $beforeSnapshot,
                        'after_snapshot' => $afterSnapshot,
                        'year' => date('Y'),
                    ]);

                    return redirect(AmendmentResource::getUrl('edit', ['record' => $amendment, 'sales_order' => $so->id]));
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return AmendmentsTable::configure($table);
    }
}
