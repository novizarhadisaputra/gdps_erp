<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\JhtConfig;

class JhtConfigPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:JhtConfig');
    }

    public function view(AuthUser $authUser, JhtConfig $jhtConfig): bool
    {
        return $authUser->can('View:JhtConfig');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:JhtConfig');
    }

    public function update(AuthUser $authUser, JhtConfig $jhtConfig): bool
    {
        return $authUser->can('Update:JhtConfig');
    }

    public function delete(AuthUser $authUser, JhtConfig $jhtConfig): bool
    {
        return $authUser->can('Delete:JhtConfig');
    }

    public function restore(AuthUser $authUser, JhtConfig $jhtConfig): bool
    {
        return $authUser->can('Restore:JhtConfig');
    }

    public function forceDelete(AuthUser $authUser, JhtConfig $jhtConfig): bool
    {
        return $authUser->can('ForceDelete:JhtConfig');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:JhtConfig');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:JhtConfig');
    }

    public function replicate(AuthUser $authUser, JhtConfig $jhtConfig): bool
    {
        return $authUser->can('Replicate:JhtConfig');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:JhtConfig');
    }
}
