<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\ThrBasisType;

class ThrBasisTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ThrBasisType');
    }

    public function view(AuthUser $authUser, ThrBasisType $thrBasisType): bool
    {
        return $authUser->can('View:ThrBasisType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ThrBasisType');
    }

    public function update(AuthUser $authUser, ThrBasisType $thrBasisType): bool
    {
        return $authUser->can('Update:ThrBasisType');
    }

    public function delete(AuthUser $authUser, ThrBasisType $thrBasisType): bool
    {
        return $authUser->can('Delete:ThrBasisType');
    }

    public function restore(AuthUser $authUser, ThrBasisType $thrBasisType): bool
    {
        return $authUser->can('Restore:ThrBasisType');
    }

    public function forceDelete(AuthUser $authUser, ThrBasisType $thrBasisType): bool
    {
        return $authUser->can('ForceDelete:ThrBasisType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ThrBasisType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ThrBasisType');
    }

    public function replicate(AuthUser $authUser, ThrBasisType $thrBasisType): bool
    {
        return $authUser->can('Replicate:ThrBasisType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ThrBasisType');
    }
}
