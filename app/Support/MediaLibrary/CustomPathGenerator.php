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
        $modelType = $media->model_type;

        // Check if model is within a Module
        if (str_contains($modelType, 'Modules\\')) {
            // Pattern: Modules\{Module}\Models\{Model}
            // Example: Modules\CRM\Models\GeneralInformation
            $parts = explode('\\', $modelType);

            if (count($parts) >= 4) {
                $module = strtolower($parts[1]); // crm
                $model = strtolower(\Illuminate\Support\Str::plural(class_basename($modelType))); // general_informations (snake) or general-informations (kebab)?
                // Let's use kebab case for URLs/folders
                $model = \Illuminate\Support\Str::kebab(\Illuminate\Support\Str::plural(class_basename($modelType)));

                // Format: module/model/id
                // Example: crm/general-informations/123
                return "{$module}/{$model}/{$media->collection_name}";
            }
        }

        // Default or non-module models
        $prefix = config('media-library.path_generator_prefix', 'media');

        return $prefix.'/'.$media->collection_name;
    }
}
