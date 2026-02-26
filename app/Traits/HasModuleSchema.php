<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasModuleSchema
{
    /**
     * Get the table associated with the model, prefixed with the module schema.
     */
    public function getTable(): string
    {
        $table = parent::getTable();

        // If table already has a dot, it likely already has a schema
        if (Str::contains($table, '.')) {
            return $table;
        }

        $className = get_class($this);

        // If model is in a Module (Modules\{ModuleName}\Models\...)
        if (Str::startsWith($className, 'Modules\\')) {
            $parts = explode('\\', $className);
            if (isset($parts[1])) {
                $moduleName = Str::snake($parts[1]);

                return ($parts[1] === 'CRM' ? 'crm' : $moduleName).".{$table}";
            }
        }

        return $table;
    }
}
