<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\RegencyMinimumWage;

class RegencyMinimumWagePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RegencyMinimumWage');
    }

    public function view(AuthUser $authUser, RegencyMinimumWage $regencyMinimumWage): bool
    {
        return $authUser->can('View:RegencyMinimumWage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RegencyMinimumWage');
    }

    public function update(AuthUser $authUser, RegencyMinimumWage $regencyMinimumWage): bool
    {
        return $authUser->can('Update:RegencyMinimumWage');
    }

    public function delete(AuthUser $authUser, RegencyMinimumWage $regencyMinimumWage): bool
    {
        return $authUser->can('Delete:RegencyMinimumWage');
    }

    public function restore(AuthUser $authUser, RegencyMinimumWage $regencyMinimumWage): bool
    {
        return $authUser->can('Restore:RegencyMinimumWage');
    }

    public function forceDelete(AuthUser $authUser, RegencyMinimumWage $regencyMinimumWage): bool
    {
        return $authUser->can('ForceDelete:RegencyMinimumWage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RegencyMinimumWage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RegencyMinimumWage');
    }

    public function replicate(AuthUser $authUser, RegencyMinimumWage $regencyMinimumWage): bool
    {
        return $authUser->can('Replicate:RegencyMinimumWage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RegencyMinimumWage');
    }
}
