<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\IndustrialSector;

class IndustrialSectorPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:IndustrialSector');
    }

    public function view(AuthUser $authUser, IndustrialSector $industrialSector): bool
    {
        return $authUser->can('View:IndustrialSector');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:IndustrialSector');
    }

    public function update(AuthUser $authUser, IndustrialSector $industrialSector): bool
    {
        return $authUser->can('Update:IndustrialSector');
    }

    public function delete(AuthUser $authUser, IndustrialSector $industrialSector): bool
    {
        return $authUser->can('Delete:IndustrialSector');
    }

    public function restore(AuthUser $authUser, IndustrialSector $industrialSector): bool
    {
        return $authUser->can('Restore:IndustrialSector');
    }

    public function forceDelete(AuthUser $authUser, IndustrialSector $industrialSector): bool
    {
        return $authUser->can('ForceDelete:IndustrialSector');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:IndustrialSector');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:IndustrialSector');
    }

    public function replicate(AuthUser $authUser, IndustrialSector $industrialSector): bool
    {
        return $authUser->can('Replicate:IndustrialSector');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:IndustrialSector');
    }
}
