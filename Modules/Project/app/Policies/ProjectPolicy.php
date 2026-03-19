<?php

declare(strict_types=1);

namespace Modules\Project\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Project\Models\Project;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Project');
    }

    public function view(AuthUser $authUser, Project $project): bool
    {
        if ($authUser->can('View:Project')) {
            return true;
        }

        return $project->members()
            ->whereHasMorph('memberable', [\Modules\MasterData\Models\Employee::class, \App\Models\User::class], function ($query) use ($authUser) {
                $query->where('email', $authUser->email)
                    ->orWhere('id', $authUser->id);
            })
            ->exists();
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Project');
    }

    public function update(AuthUser $authUser, Project $project): bool
    {
        if ($authUser->can('Update:Project')) {
            return true;
        }

        // Project Managers can update their own projects
        return $project->members()
            ->where('role', 'Project Manager')
            ->whereHasMorph('memberable', [\Modules\MasterData\Models\Employee::class, \App\Models\User::class], function ($query) use ($authUser) {
                $query->where('email', $authUser->email)
                    ->orWhere('id', $authUser->id);
            })
            ->exists();
    }

    public function delete(AuthUser $authUser, Project $project): bool
    {
        return $authUser->can('Delete:Project');
    }

    public function restore(AuthUser $authUser, Project $project): bool
    {
        return $authUser->can('Restore:Project');
    }

    public function forceDelete(AuthUser $authUser, Project $project): bool
    {
        return $authUser->can('ForceDelete:Project');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Project');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Project');
    }

    public function replicate(AuthUser $authUser, Project $project): bool
    {
        return $authUser->can('Replicate:Project');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Project');
    }
}
