<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\NonFixedAllowance;

class NonFixedAllowancePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:NonFixedAllowance');
    }

    public function view(AuthUser $authUser, NonFixedAllowance $nonFixedAllowance): bool
    {
        return $authUser->can('View:NonFixedAllowance');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:NonFixedAllowance');
    }

    public function update(AuthUser $authUser, NonFixedAllowance $nonFixedAllowance): bool
    {
        return $authUser->can('Update:NonFixedAllowance');
    }

    public function delete(AuthUser $authUser, NonFixedAllowance $nonFixedAllowance): bool
    {
        return $authUser->can('Delete:NonFixedAllowance');
    }

    public function restore(AuthUser $authUser, NonFixedAllowance $nonFixedAllowance): bool
    {
        return $authUser->can('Restore:NonFixedAllowance');
    }

    public function forceDelete(AuthUser $authUser, NonFixedAllowance $nonFixedAllowance): bool
    {
        return $authUser->can('ForceDelete:NonFixedAllowance');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:NonFixedAllowance');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:NonFixedAllowance');
    }

    public function replicate(AuthUser $authUser, NonFixedAllowance $nonFixedAllowance): bool
    {
        return $authUser->can('Replicate:NonFixedAllowance');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:NonFixedAllowance');
    }
}
