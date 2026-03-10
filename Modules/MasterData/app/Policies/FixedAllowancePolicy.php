<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\FixedAllowance;

class FixedAllowancePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FixedAllowance');
    }

    public function view(AuthUser $authUser, FixedAllowance $fixedAllowance): bool
    {
        return $authUser->can('View:FixedAllowance');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FixedAllowance');
    }

    public function update(AuthUser $authUser, FixedAllowance $fixedAllowance): bool
    {
        return $authUser->can('Update:FixedAllowance');
    }

    public function delete(AuthUser $authUser, FixedAllowance $fixedAllowance): bool
    {
        return $authUser->can('Delete:FixedAllowance');
    }

    public function restore(AuthUser $authUser, FixedAllowance $fixedAllowance): bool
    {
        return $authUser->can('Restore:FixedAllowance');
    }

    public function forceDelete(AuthUser $authUser, FixedAllowance $fixedAllowance): bool
    {
        return $authUser->can('ForceDelete:FixedAllowance');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FixedAllowance');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FixedAllowance');
    }

    public function replicate(AuthUser $authUser, FixedAllowance $fixedAllowance): bool
    {
        return $authUser->can('Replicate:FixedAllowance');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FixedAllowance');
    }
}
