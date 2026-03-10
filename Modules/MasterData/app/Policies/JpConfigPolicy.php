<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\JpConfig;

class JpConfigPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:JpConfig');
    }

    public function view(AuthUser $authUser, JpConfig $jpConfig): bool
    {
        return $authUser->can('View:JpConfig');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:JpConfig');
    }

    public function update(AuthUser $authUser, JpConfig $jpConfig): bool
    {
        return $authUser->can('Update:JpConfig');
    }

    public function delete(AuthUser $authUser, JpConfig $jpConfig): bool
    {
        return $authUser->can('Delete:JpConfig');
    }

    public function restore(AuthUser $authUser, JpConfig $jpConfig): bool
    {
        return $authUser->can('Restore:JpConfig');
    }

    public function forceDelete(AuthUser $authUser, JpConfig $jpConfig): bool
    {
        return $authUser->can('ForceDelete:JpConfig');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:JpConfig');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:JpConfig');
    }

    public function replicate(AuthUser $authUser, JpConfig $jpConfig): bool
    {
        return $authUser->can('Replicate:JpConfig');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:JpConfig');
    }
}
