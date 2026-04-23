<?php

namespace App\Traits;

trait HasModuleSchema
{
    /**
     * Get the table associated with the model, prefixed with the module schema.
     */
    /**
     * Boot the trait to apply a global scope for PostgreSQL schema handling.
     */
    public static function bootHasModuleSchema(): void
    {
        if (config('database.default') === 'pgsql') {
            static::addGlobalScope('postgres_schema', function ($builder) {
                $model = $builder->getModel();
                $baseTable = $model->getTable();
                $schemaTable = $model->getSchemaPrefixedTable();

                if (str_contains($schemaTable, '.')) {
                    $builder->from($schemaTable, $baseTable);
                }
            });
        }
    }

    /**
     * Get the table name prefixed with the module schema.
     */
    public function getSchemaPrefixedTable(): string
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
                $table = "master_data.{$table}";
            } elseif (str_contains($class, 'Modules\Finance')) {
                $table = "finance.{$table}";
            } elseif (str_contains($class, 'Modules\CRM')) {
                $table = "crm.{$table}";
            } elseif (str_contains($class, 'Modules\Project')) {
                $table = "project.{$table}";
            }
        }

        return $table;
    }

    /**
     * Overriding getTable to return a clean alias (no dots) for PostgreSQL.
     * The actual schema-prefixed table is handled by the global scope.
     */
    public function getTable(): string
    {
        $table = parent::getTable();

        if (str_contains($table, '.')) {
            return last(explode('.', $table));
        }

        return $table;
    }

    /**
     * Qualify the given column name by the table name.
     */
    public function qualifyColumn($column): string
    {
        if (str_contains($column, '.')) {
            return $column;
        }

        return $this->getTable().'.'.$column;
    }
}
