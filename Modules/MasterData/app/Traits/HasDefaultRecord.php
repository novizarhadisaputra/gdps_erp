<?php

namespace Modules\MasterData\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasDefaultRecord
{
    public static function bootHasDefaultRecord(): void
    {
        static::saving(function (Model $model) {
            // If the record is being set as default
            if ($model->is_default) {
                // Set all other records to NOT be default
                // We use a query builder to avoid triggering more events
                static::query()
                    ->where('id', '!=', $model->id)
                    ->where('is_default', true)
                    ->when($model->isDefaultScoped(), function (Builder $query) use ($model) {
                        foreach ($model->getDefaultScopeColumns() as $column) {
                            $query->where($column, $model->{$column});
                        }
                    })
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Determine if the default record exclusivity is scoped to specific columns.
     */
    protected function isDefaultScoped(): bool
    {
        return ! empty($this->getDefaultScopeColumns());
    }

    /**
     * Get the columns that define the scope for default record exclusivity.
     * Override this in the model if needed (e.g., ['category_id']).
     */
    protected function getDefaultScopeColumns(): array
    {
        return [];
    }

    /**
     * Scope a query to only include the default record.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }
}
