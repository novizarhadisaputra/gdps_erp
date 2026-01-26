<?php

namespace Modules\MasterData\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasUnitScoping
{
    /**
     * Boot the trait.
     */
    public static function bootHasUnitScoping(): void
    {
        static::creating(function ($model) {
            if (empty($model->unit_id) && Auth::check()) {
                $model->unit_id = Auth::user()->unit_id;
            }
        });

        static::addGlobalScope('unit_scope', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();

                // If user has 'super_admin' role, they can see everything.
                if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
                    return;
                }

                // Also check for a specific permission to bypass unit scoping
                if ($user->can('view_all_master_data')) {
                    return;
                }

                $builder->where('unit_id', $user->unit_id);
            }
        });
    }
}
