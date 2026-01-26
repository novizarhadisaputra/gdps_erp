<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\ProjectArea;

class ProjectAreaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProjectArea');
    }

    public function view(AuthUser $authUser, ProjectArea $projectArea): bool
    {
        return $authUser->can('View:ProjectArea');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProjectArea');
    }

    public function update(AuthUser $authUser, ProjectArea $projectArea): bool
    {
        return $authUser->can('Update:ProjectArea');
    }

    public function delete(AuthUser $authUser, ProjectArea $projectArea): bool
    {
        return $authUser->can('Delete:ProjectArea');
    }

    public function restore(AuthUser $authUser, ProjectArea $projectArea): bool
    {
        return $authUser->can('Restore:ProjectArea');
    }

    public function forceDelete(AuthUser $authUser, ProjectArea $projectArea): bool
    {
        return $authUser->can('ForceDelete:ProjectArea');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProjectArea');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProjectArea');
    }

    public function replicate(AuthUser $authUser, ProjectArea $projectArea): bool
    {
        return $authUser->can('Replicate:ProjectArea');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProjectArea');
    }
}
