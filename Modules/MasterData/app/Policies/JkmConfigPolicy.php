<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\JkmConfig;

class JkmConfigPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:JkmConfig');
    }

    public function view(AuthUser $authUser, JkmConfig $jkmConfig): bool
    {
        return $authUser->can('View:JkmConfig');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:JkmConfig');
    }

    public function update(AuthUser $authUser, JkmConfig $jkmConfig): bool
    {
        return $authUser->can('Update:JkmConfig');
    }

    public function delete(AuthUser $authUser, JkmConfig $jkmConfig): bool
    {
        return $authUser->can('Delete:JkmConfig');
    }

    public function restore(AuthUser $authUser, JkmConfig $jkmConfig): bool
    {
        return $authUser->can('Restore:JkmConfig');
    }

    public function forceDelete(AuthUser $authUser, JkmConfig $jkmConfig): bool
    {
        return $authUser->can('ForceDelete:JkmConfig');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:JkmConfig');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:JkmConfig');
    }

    public function replicate(AuthUser $authUser, JkmConfig $jkmConfig): bool
    {
        return $authUser->can('Replicate:JkmConfig');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:JkmConfig');
    }
}
