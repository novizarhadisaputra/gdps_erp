<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\DirectCostCategory;

class DirectCostCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DirectCostCategory');
    }

    public function view(AuthUser $authUser, DirectCostCategory $directCostCategory): bool
    {
        return $authUser->can('View:DirectCostCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DirectCostCategory');
    }

    public function update(AuthUser $authUser, DirectCostCategory $directCostCategory): bool
    {
        return $authUser->can('Update:DirectCostCategory');
    }

    public function delete(AuthUser $authUser, DirectCostCategory $directCostCategory): bool
    {
        return $authUser->can('Delete:DirectCostCategory');
    }

    public function restore(AuthUser $authUser, DirectCostCategory $directCostCategory): bool
    {
        return $authUser->can('Restore:DirectCostCategory');
    }

    public function forceDelete(AuthUser $authUser, DirectCostCategory $directCostCategory): bool
    {
        return $authUser->can('ForceDelete:DirectCostCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DirectCostCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DirectCostCategory');
    }

    public function replicate(AuthUser $authUser, DirectCostCategory $directCostCategory): bool
    {
        return $authUser->can('Replicate:DirectCostCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DirectCostCategory');
    }
}
