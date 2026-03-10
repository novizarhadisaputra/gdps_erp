<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\BufferCostType;

class BufferCostTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BufferCostType');
    }

    public function view(AuthUser $authUser, BufferCostType $bufferCostType): bool
    {
        return $authUser->can('View:BufferCostType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BufferCostType');
    }

    public function update(AuthUser $authUser, BufferCostType $bufferCostType): bool
    {
        return $authUser->can('Update:BufferCostType');
    }

    public function delete(AuthUser $authUser, BufferCostType $bufferCostType): bool
    {
        return $authUser->can('Delete:BufferCostType');
    }

    public function restore(AuthUser $authUser, BufferCostType $bufferCostType): bool
    {
        return $authUser->can('Restore:BufferCostType');
    }

    public function forceDelete(AuthUser $authUser, BufferCostType $bufferCostType): bool
    {
        return $authUser->can('ForceDelete:BufferCostType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BufferCostType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BufferCostType');
    }

    public function replicate(AuthUser $authUser, BufferCostType $bufferCostType): bool
    {
        return $authUser->can('Replicate:BufferCostType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BufferCostType');
    }
}
