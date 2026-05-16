<?php

namespace App\Traits;

trait ParsesCurrency
{
    /**
     * Parse a masked currency string into a float value.
     *
     * @param  mixed  $value
     */
    protected static function parseCurrency($value): float
    {
        if (! $value) {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        // Standard masking replacement (replace dots with nothing, commas with dots)
        return (float) str_replace(['.', ','], ['', '.'], $value);
    }
}
