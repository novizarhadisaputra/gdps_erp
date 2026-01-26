<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\UnitOfMeasure;

class UnitOfMeasurePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:UnitOfMeasure');
    }

    public function view(AuthUser $authUser, UnitOfMeasure $unitOfMeasure): bool
    {
        return $authUser->can('View:UnitOfMeasure');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:UnitOfMeasure');
    }

    public function update(AuthUser $authUser, UnitOfMeasure $unitOfMeasure): bool
    {
        return $authUser->can('Update:UnitOfMeasure');
    }

    public function delete(AuthUser $authUser, UnitOfMeasure $unitOfMeasure): bool
    {
        return $authUser->can('Delete:UnitOfMeasure');
    }

    public function restore(AuthUser $authUser, UnitOfMeasure $unitOfMeasure): bool
    {
        return $authUser->can('Restore:UnitOfMeasure');
    }

    public function forceDelete(AuthUser $authUser, UnitOfMeasure $unitOfMeasure): bool
    {
        return $authUser->can('ForceDelete:UnitOfMeasure');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:UnitOfMeasure');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:UnitOfMeasure');
    }

    public function replicate(AuthUser $authUser, UnitOfMeasure $unitOfMeasure): bool
    {
        return $authUser->can('Replicate:UnitOfMeasure');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:UnitOfMeasure');
    }
}
