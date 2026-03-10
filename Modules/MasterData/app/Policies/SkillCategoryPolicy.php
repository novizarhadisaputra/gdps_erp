<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\SkillCategory;

class SkillCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SkillCategory');
    }

    public function view(AuthUser $authUser, SkillCategory $skillCategory): bool
    {
        return $authUser->can('View:SkillCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SkillCategory');
    }

    public function update(AuthUser $authUser, SkillCategory $skillCategory): bool
    {
        return $authUser->can('Update:SkillCategory');
    }

    public function delete(AuthUser $authUser, SkillCategory $skillCategory): bool
    {
        return $authUser->can('Delete:SkillCategory');
    }

    public function restore(AuthUser $authUser, SkillCategory $skillCategory): bool
    {
        return $authUser->can('Restore:SkillCategory');
    }

    public function forceDelete(AuthUser $authUser, SkillCategory $skillCategory): bool
    {
        return $authUser->can('ForceDelete:SkillCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SkillCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SkillCategory');
    }

    public function replicate(AuthUser $authUser, SkillCategory $skillCategory): bool
    {
        return $authUser->can('Replicate:SkillCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SkillCategory');
    }
}
