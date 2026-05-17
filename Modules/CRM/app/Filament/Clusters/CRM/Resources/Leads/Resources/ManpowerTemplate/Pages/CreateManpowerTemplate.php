<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\ManpowerTemplateResource;

class CreateManpowerTemplate extends CreateRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ManpowerTemplateResource::class;

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $latestGi = $this->parentRecord->generalInformations()->latest('created_at')->first();

        $this->form->fill([
            'name' => $this->parentRecord->customer?->name.' Manpower',
            'description' => $latestGi?->scope_of_work,
            'project_area_id' => $latestGi?->project_area_id ?? $this->parentRecord->project_area_id,
            'work_scheme_id' => $latestGi?->work_scheme_id ?? $this->parentRecord->work_scheme_id,
            'year' => $this->parentRecord->start_date ? \Carbon\Carbon::parse($this->parentRecord->start_date)->year : date('Y'),
            'clusters' => [
                [
                    'product_cluster_id' => $this->parentRecord->product_cluster_id,
                ]
            ],
        ]);

        $this->callHook('afterFill');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['lead_id'] = $this->parentRecord->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['lead' => $this->parentRecord, 'record' => $this->getRecord()]);
    }
}
