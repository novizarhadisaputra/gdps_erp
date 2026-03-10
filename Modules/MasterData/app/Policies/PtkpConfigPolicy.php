<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\PtkpConfig;

class PtkpConfigPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PtkpConfig');
    }

    public function view(AuthUser $authUser, PtkpConfig $ptkpConfig): bool
    {
        return $authUser->can('View:PtkpConfig');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PtkpConfig');
    }

    public function update(AuthUser $authUser, PtkpConfig $ptkpConfig): bool
    {
        return $authUser->can('Update:PtkpConfig');
    }

    public function delete(AuthUser $authUser, PtkpConfig $ptkpConfig): bool
    {
        return $authUser->can('Delete:PtkpConfig');
    }

    public function restore(AuthUser $authUser, PtkpConfig $ptkpConfig): bool
    {
        return $authUser->can('Restore:PtkpConfig');
    }

    public function forceDelete(AuthUser $authUser, PtkpConfig $ptkpConfig): bool
    {
        return $authUser->can('ForceDelete:PtkpConfig');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PtkpConfig');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PtkpConfig');
    }

    public function replicate(AuthUser $authUser, PtkpConfig $ptkpConfig): bool
    {
        return $authUser->can('Replicate:PtkpConfig');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PtkpConfig');
    }
}
