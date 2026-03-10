<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\BpjsBasisType;

class BpjsBasisTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BpjsBasisType');
    }

    public function view(AuthUser $authUser, BpjsBasisType $bpjsBasisType): bool
    {
        return $authUser->can('View:BpjsBasisType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BpjsBasisType');
    }

    public function update(AuthUser $authUser, BpjsBasisType $bpjsBasisType): bool
    {
        return $authUser->can('Update:BpjsBasisType');
    }

    public function delete(AuthUser $authUser, BpjsBasisType $bpjsBasisType): bool
    {
        return $authUser->can('Delete:BpjsBasisType');
    }

    public function restore(AuthUser $authUser, BpjsBasisType $bpjsBasisType): bool
    {
        return $authUser->can('Restore:BpjsBasisType');
    }

    public function forceDelete(AuthUser $authUser, BpjsBasisType $bpjsBasisType): bool
    {
        return $authUser->can('ForceDelete:BpjsBasisType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BpjsBasisType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BpjsBasisType');
    }

    public function replicate(AuthUser $authUser, BpjsBasisType $bpjsBasisType): bool
    {
        return $authUser->can('Replicate:BpjsBasisType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BpjsBasisType');
    }
}
