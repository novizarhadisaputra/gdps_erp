<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\Tax;

class TaxPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Tax');
    }

    public function view(AuthUser $authUser, Tax $tax): bool
    {
        return $authUser->can('View:Tax');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Tax');
    }

    public function update(AuthUser $authUser, Tax $tax): bool
    {
        return $authUser->can('Update:Tax');
    }

    public function delete(AuthUser $authUser, Tax $tax): bool
    {
        return $authUser->can('Delete:Tax');
    }

    public function restore(AuthUser $authUser, Tax $tax): bool
    {
        return $authUser->can('Restore:Tax');
    }

    public function forceDelete(AuthUser $authUser, Tax $tax): bool
    {
        return $authUser->can('ForceDelete:Tax');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Tax');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Tax');
    }

    public function replicate(AuthUser $authUser, Tax $tax): bool
    {
        return $authUser->can('Replicate:Tax');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Tax');
    }
}
