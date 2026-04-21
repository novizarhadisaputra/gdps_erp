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
            $unified[] = array_merge($item, ['type' => 'item']);
        }

        // Add Manpower
        foreach ($after['manpower_details'] ?? [] as $mp) {
            $unified[] = array_merge($mp, [
                'type' => 'personnel',
                'description' => $mp['job_position_name'] ?? '',
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
