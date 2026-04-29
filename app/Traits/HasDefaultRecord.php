<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasDefaultRecord
{
    public static function bootHasDefaultRecord(): void
    {
        static::saving(function (Model $model) {
            if ($model->is_default) {
                // Set all other records to is_default = false
                static::query()
                    ->where('id', '!=', $model->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
