<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\JkkConfig;

class JkkConfigPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:JkkConfig');
    }

    public function view(AuthUser $authUser, JkkConfig $jkkConfig): bool
    {
        return $authUser->can('View:JkkConfig');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:JkkConfig');
    }

    public function update(AuthUser $authUser, JkkConfig $jkkConfig): bool
    {
        return $authUser->can('Update:JkkConfig');
    }

    public function delete(AuthUser $authUser, JkkConfig $jkkConfig): bool
    {
        return $authUser->can('Delete:JkkConfig');
    }

    public function restore(AuthUser $authUser, JkkConfig $jkkConfig): bool
    {
        return $authUser->can('Restore:JkkConfig');
    }

    public function forceDelete(AuthUser $authUser, JkkConfig $jkkConfig): bool
    {
        return $authUser->can('ForceDelete:JkkConfig');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:JkkConfig');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:JkkConfig');
    }

    public function replicate(AuthUser $authUser, JkkConfig $jkkConfig): bool
    {
        return $authUser->can('Replicate:JkkConfig');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:JkkConfig');
    }
}
