<?php

namespace App\Traits;

trait HasModuleSchema
{
    /**
     * Get the table associated with the model, prefixed with the module schema.
     */
    public function getTable(): string
    {
        $table = parent::getTable();

        // If SQLite, don't use schema prefix
        if (config('database.default') === 'sqlite') {
            return $table;
        }

        // Determine schema from namespace if not already prefixed
        if (! str_contains($table, '.')) {
            $class = get_class($this);
            if (str_contains($class, 'Modules\MasterData')) {
                return "master_data.{$table}";
            }
            if (str_contains($class, 'Modules\Finance')) {
                return "finance.{$table}";
            }
            if (str_contains($class, 'Modules\CRM')) {
                return "crm.{$table}";
            }
            if (str_contains($class, 'Modules\Project')) {
                return "project.{$table}";
            }
        }

        return $table;
    }
}
