<?php

namespace App\Support\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    /*
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media).'/';
    }

    /*
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    /*
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }

    /*
     * Get a unique base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        $prefix = config('media-library.path_generator_prefix', 'media');
        
        $map = [
            'Modules\Finance\Models\ProfitabilityAnalysis' => "finance/profitability-analyses/{$media->model_id}",
            'Modules\CRM\Models\Contract' => "crm/contracts/{$media->model_id}",
            'Modules\CRM\Models\Proposal' => "crm/proposals/{$media->model_id}",
            'Modules\CRM\Models\GeneralInformation' => "crm/general-informations/{$media->model_id}",
            'Modules\MasterData\Models\Customer' => "masterdata/customers/{$media->model_id}",
            'Modules\MasterData\Models\Employee' => "masterdata/employees/{$media->model_id}",
            'Modules\Project\Models\Project' => "project/projects/{$media->model_id}",
        ];

        if (isset($map[$media->model_type])) {
            return "{$map[$media->model_type]}/{$media->collection_name}/{$media->id}";
        }

        return $prefix.'/'.$media->id;
    }
}
