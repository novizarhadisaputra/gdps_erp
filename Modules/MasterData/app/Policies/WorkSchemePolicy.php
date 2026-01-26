<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\WorkScheme;

class WorkSchemePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WorkScheme');
    }

    public function view(AuthUser $authUser, WorkScheme $workScheme): bool
    {
        return $authUser->can('View:WorkScheme');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WorkScheme');
    }

    public function update(AuthUser $authUser, WorkScheme $workScheme): bool
    {
        return $authUser->can('Update:WorkScheme');
    }

    public function delete(AuthUser $authUser, WorkScheme $workScheme): bool
    {
        return $authUser->can('Delete:WorkScheme');
    }

    public function restore(AuthUser $authUser, WorkScheme $workScheme): bool
    {
        return $authUser->can('Restore:WorkScheme');
    }

    public function forceDelete(AuthUser $authUser, WorkScheme $workScheme): bool
    {
        return $authUser->can('ForceDelete:WorkScheme');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WorkScheme');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WorkScheme');
    }

    public function replicate(AuthUser $authUser, WorkScheme $workScheme): bool
    {
        return $authUser->can('Replicate:WorkScheme');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WorkScheme');
    }
}
