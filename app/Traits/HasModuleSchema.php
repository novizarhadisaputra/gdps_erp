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
        static $resolvedTables = [];
        $class = get_class($this);
        $connection = config('database.default');
        $cacheKey = "{$class}_{$connection}";

        if (isset($resolvedTables[$cacheKey])) {
            return $resolvedTables[$cacheKey];
        }

        // Get the base table name without calling parent::getTable() to avoid recursion if overridden
        $table = $this->table ?? \Illuminate\Support\Str::snake(\Illuminate\Support\Str::pluralStudly(class_basename($this)));

        // Determine module prefix
        $module = null;
        if (str_contains($class, 'Modules\MasterData')) {
            $module = 'master_data';
        } elseif (str_contains($class, 'Modules\Finance')) {
            $module = 'finance';
        } elseif (str_contains($class, 'Modules\CRM')) {
            $module = 'crm';
        } elseif (str_contains($class, 'Modules\Project')) {
            $module = 'project';
        } elseif (str_contains($class, 'Modules\Logistics')) {
            $module = 'logistics';
        }

        $result = $table;

        if ($module) {
            if ($connection === 'sqlite') {
                // If it already has a dot (e.g. 'project.projects' in $table)
                if (str_contains($table, '.')) {
                    $result = str_replace('.', '_', $table);
                } else {
                    // Prepend module if not already present
                    $prefix = "{$module}_";
                    if (! str_starts_with($table, $prefix)) {
                        $result = "{$prefix}{$table}";
                    }
                }
            } else {
                // For PostgreSQL
                if (str_contains($table, '.')) {
                    $result = $table;
                } else {
                    $result = "{$module}.{$table}";
                }
            }
        }

        $resolvedTables[$cacheKey] = $result;

        return $result;
    }

    /**
     * Overriding getTable to return a clean alias (no dots) for PostgreSQL.
     * The actual schema-prefixed table is handled by the global scope.
     */
    public function getTable(): string
    {
        if (config('database.default') === 'sqlite') {
            return $this->getSchemaPrefixedTable();
        }

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
