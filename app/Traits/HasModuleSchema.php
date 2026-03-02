<?php

namespace App\Traits;

trait HasModuleSchema
{
    /**
     * Get the table associated with the model, prefixed with the module schema.
     */
    public function getTable(): string
    {
        return parent::getTable();
    }
}
