<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Database\Eloquent\Factories\Factory::guessFactoryNamesUsing(function (string $modelName) {
            // Modular behavior for Modules\X\Models
            if (str_starts_with($modelName, 'Modules\\')) {
                $parts = explode('\\', $modelName);
                $module = $parts[1] ?? null;
                $model = end($parts);

                if ($module && $model) {
                    $factoryClass = "Modules\\{$module}\\Database\\Factories\\{$model}Factory";
                    if (class_exists($factoryClass)) {
                        return $factoryClass;
                    }
                }
            }

            // Default fallback
            return 'Database\\Factories\\'.class_basename($modelName).'Factory';
        });
    }
}
