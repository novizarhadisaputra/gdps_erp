<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\AssetGroup;

class AssetGroupPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AssetGroup');
    }

    public function view(AuthUser $authUser, AssetGroup $assetGroup): bool
    {
        return $authUser->can('View:AssetGroup');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AssetGroup');
    }

    public function update(AuthUser $authUser, AssetGroup $assetGroup): bool
    {
        return $authUser->can('Update:AssetGroup');
    }

    public function delete(AuthUser $authUser, AssetGroup $assetGroup): bool
    {
        return $authUser->can('Delete:AssetGroup');
    }

    public function restore(AuthUser $authUser, AssetGroup $assetGroup): bool
    {
        return $authUser->can('Restore:AssetGroup');
    }

    public function forceDelete(AuthUser $authUser, AssetGroup $assetGroup): bool
    {
        return $authUser->can('ForceDelete:AssetGroup');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AssetGroup');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AssetGroup');
    }

    public function replicate(AuthUser $authUser, AssetGroup $assetGroup): bool
    {
        return $authUser->can('Replicate:AssetGroup');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AssetGroup');
    }
}
