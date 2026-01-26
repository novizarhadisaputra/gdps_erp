<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\ProjectType;

class ProjectTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProjectType');
    }

    public function view(AuthUser $authUser, ProjectType $projectType): bool
    {
        return $authUser->can('View:ProjectType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProjectType');
    }

    public function update(AuthUser $authUser, ProjectType $projectType): bool
    {
        return $authUser->can('Update:ProjectType');
    }

    public function delete(AuthUser $authUser, ProjectType $projectType): bool
    {
        return $authUser->can('Delete:ProjectType');
    }

    public function restore(AuthUser $authUser, ProjectType $projectType): bool
    {
        return $authUser->can('Restore:ProjectType');
    }

    public function forceDelete(AuthUser $authUser, ProjectType $projectType): bool
    {
        return $authUser->can('ForceDelete:ProjectType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProjectType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProjectType');
    }

    public function replicate(AuthUser $authUser, ProjectType $projectType): bool
    {
        return $authUser->can('Replicate:ProjectType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProjectType');
    }
}
