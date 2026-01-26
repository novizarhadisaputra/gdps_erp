<?php

declare(strict_types=1);

namespace Modules\Project\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Project\Models\ProjectInformation;

class ProjectInformationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProjectInformation');
    }

    public function view(AuthUser $authUser, ProjectInformation $projectInformation): bool
    {
        return $authUser->can('View:ProjectInformation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProjectInformation');
    }

    public function update(AuthUser $authUser, ProjectInformation $projectInformation): bool
    {
        return $authUser->can('Update:ProjectInformation');
    }

    public function delete(AuthUser $authUser, ProjectInformation $projectInformation): bool
    {
        return $authUser->can('Delete:ProjectInformation');
    }

    public function restore(AuthUser $authUser, ProjectInformation $projectInformation): bool
    {
        return $authUser->can('Restore:ProjectInformation');
    }

    public function forceDelete(AuthUser $authUser, ProjectInformation $projectInformation): bool
    {
        return $authUser->can('ForceDelete:ProjectInformation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProjectInformation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProjectInformation');
    }

    public function replicate(AuthUser $authUser, ProjectInformation $projectInformation): bool
    {
        return $authUser->can('Replicate:ProjectInformation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProjectInformation');
    }
}
