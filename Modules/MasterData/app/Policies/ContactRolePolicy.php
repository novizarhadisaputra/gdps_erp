<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\ContactRole;

class ContactRolePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ContactRole');
    }

    public function view(AuthUser $authUser, ContactRole $contactRole): bool
    {
        return $authUser->can('View:ContactRole');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ContactRole');
    }

    public function update(AuthUser $authUser, ContactRole $contactRole): bool
    {
        return $authUser->can('Update:ContactRole');
    }

    public function delete(AuthUser $authUser, ContactRole $contactRole): bool
    {
        return $authUser->can('Delete:ContactRole');
    }

    public function restore(AuthUser $authUser, ContactRole $contactRole): bool
    {
        return $authUser->can('Restore:ContactRole');
    }

    public function forceDelete(AuthUser $authUser, ContactRole $contactRole): bool
    {
        return $authUser->can('ForceDelete:ContactRole');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ContactRole');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ContactRole');
    }

    public function replicate(AuthUser $authUser, ContactRole $contactRole): bool
    {
        return $authUser->can('Replicate:ContactRole');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ContactRole');
    }
}
