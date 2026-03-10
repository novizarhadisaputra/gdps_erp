<?php

declare(strict_types=1);

namespace Modules\Finance\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Finance\Models\ProfitabilityThreshold;

class ProfitabilityThresholdPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProfitabilityThreshold');
    }

    public function view(AuthUser $authUser, ProfitabilityThreshold $profitabilityThreshold): bool
    {
        return $authUser->can('View:ProfitabilityThreshold');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProfitabilityThreshold');
    }

    public function update(AuthUser $authUser, ProfitabilityThreshold $profitabilityThreshold): bool
    {
        return $authUser->can('Update:ProfitabilityThreshold');
    }

    public function delete(AuthUser $authUser, ProfitabilityThreshold $profitabilityThreshold): bool
    {
        return $authUser->can('Delete:ProfitabilityThreshold');
    }

    public function restore(AuthUser $authUser, ProfitabilityThreshold $profitabilityThreshold): bool
    {
        return $authUser->can('Restore:ProfitabilityThreshold');
    }

    public function forceDelete(AuthUser $authUser, ProfitabilityThreshold $profitabilityThreshold): bool
    {
        return $authUser->can('ForceDelete:ProfitabilityThreshold');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProfitabilityThreshold');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProfitabilityThreshold');
    }

    public function replicate(AuthUser $authUser, ProfitabilityThreshold $profitabilityThreshold): bool
    {
        return $authUser->can('Replicate:ProfitabilityThreshold');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProfitabilityThreshold');
    }
}
