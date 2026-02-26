<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class DatabaseSchemaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only run this if we are using pgsql
        if (config('database.default') !== 'pgsql') {
            return;
        }

        // We only really need to ensure schemas exist during migrations or in local/dev
        if (app()->runningInConsole()) {
            $schemas = ['crm', 'finance', 'master_data', 'project'];

            foreach ($schemas as $schema) {
                DB::statement("CREATE SCHEMA IF NOT EXISTS {$schema}");
            }
        }
    }
}
