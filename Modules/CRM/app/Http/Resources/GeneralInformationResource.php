<?php

namespace Modules\CRM\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralInformationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_number' => $this->document_number,
            'customer' => [
                'id' => $this->customer_id,
                'name' => $this->whenLoaded('customer', fn () => $this->customer->name),
            ],
            'lead' => [
                'id' => $this->lead_id,
                'title' => $this->whenLoaded('lead', fn () => $this->lead->title),
            ],
            'project_area' => [
                'id' => $this->project_area_id,
                'name' => $this->whenLoaded('projectArea', fn () => $this->projectArea->name),
            ],
            'status' => $this->status,
            'rr_status' => $this->rr_status,
            'rr_document_number' => $this->rr_document_number,
            'documents' => [
                'tor' => $this->getFirstMedia('tor')?->getTemporaryUrl(now()->addMinutes(60)),
                'rfp' => $this->getFirstMedia('rfp')?->getTemporaryUrl(now()->addMinutes(60)),
                'rfi' => $this->getFirstMedia('rfi')?->getTemporaryUrl(now()->addMinutes(60)),
            ],
            'signatures_status' => $this->isFullyApproved() ? 'complete' : 'incomplete',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
