<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\HealthConfig;

class HealthConfigPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HealthConfig');
    }

    public function view(AuthUser $authUser, HealthConfig $healthConfig): bool
    {
        return $authUser->can('View:HealthConfig');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HealthConfig');
    }

    public function update(AuthUser $authUser, HealthConfig $healthConfig): bool
    {
        return $authUser->can('Update:HealthConfig');
    }

    public function delete(AuthUser $authUser, HealthConfig $healthConfig): bool
    {
        return $authUser->can('Delete:HealthConfig');
    }

    public function restore(AuthUser $authUser, HealthConfig $healthConfig): bool
    {
        return $authUser->can('Restore:HealthConfig');
    }

    public function forceDelete(AuthUser $authUser, HealthConfig $healthConfig): bool
    {
        return $authUser->can('ForceDelete:HealthConfig');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HealthConfig');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HealthConfig');
    }

    public function replicate(AuthUser $authUser, HealthConfig $healthConfig): bool
    {
        return $authUser->can('Replicate:HealthConfig');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HealthConfig');
    }
}
