<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\AmendmentResource;

class EditAmendment extends EditRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = AmendmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $after = $data['after_snapshot'] ?? [];
        $unified = [];

        // Add Items
        foreach ($after['items'] ?? [] as $item) {
            $unified[] = array_merge($item, [
                'type' => 'item',
                'unit_price' => $item['unit_price'] ?? 0,
            ]);
        }

        // Add Manpower
        foreach ($after['manpower_details'] ?? [] as $mp) {
            $unified[] = array_merge($mp, [
                'type' => 'personnel',
                'description' => $mp['job_position_name'] ?? '',
                'unit_price' => $mp['unit_price'] ?? $mp['unit_cost'] ?? 0,
                'total_price' => $mp['total_monthly_cost'] ?? $mp['total_price'] ?? 0, // Unify key
            ]);
        }

        $data['after_snapshot_unified'] = $unified;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $unified = $data['after_snapshot_unified'] ?? [];
        $items = [];
        $manpower = [];

        foreach ($unified as $entry) {
            if (($entry['type'] ?? '') === 'personnel') {
                $manpower[] = array_merge($entry, [
                    'job_position_name' => $entry['description'] ?? '',
                    'unit_cost' => $entry['unit_price'] ?? 0, // Map for PA compatibility
                    'total_monthly_cost' => $entry['total_price'] ?? 0, // Map back
                ]);
            } else {
                $items[] = $entry;
            }
        }

        $data['after_snapshot'] = [
            'items' => $items,
            'manpower_details' => $manpower,
            'pa_revision_number' => $data['after_snapshot']['pa_revision_number'] ?? 0,
        ];

        unset($data['after_snapshot_unified']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord(), 'sales_order' => $this->getRecord()->sales_order_id]);
    }
}
