<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\ProductCluster;

class ProductClusterPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProductCluster');
    }

    public function view(AuthUser $authUser, ProductCluster $productCluster): bool
    {
        return $authUser->can('View:ProductCluster');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProductCluster');
    }

    public function update(AuthUser $authUser, ProductCluster $productCluster): bool
    {
        return $authUser->can('Update:ProductCluster');
    }

    public function delete(AuthUser $authUser, ProductCluster $productCluster): bool
    {
        return $authUser->can('Delete:ProductCluster');
    }

    public function restore(AuthUser $authUser, ProductCluster $productCluster): bool
    {
        return $authUser->can('Restore:ProductCluster');
    }

    public function forceDelete(AuthUser $authUser, ProductCluster $productCluster): bool
    {
        return $authUser->can('ForceDelete:ProductCluster');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProductCluster');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProductCluster');
    }

    public function replicate(AuthUser $authUser, ProductCluster $productCluster): bool
    {
        return $authUser->can('Replicate:ProductCluster');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProductCluster');
    }
}
