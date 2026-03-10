<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\CRM\Models\ManpowerTemplate;

class ManpowerTemplatePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ManpowerTemplate');
    }

    public function view(AuthUser $authUser, ManpowerTemplate $manpowerTemplate): bool
    {
        return $authUser->can('View:ManpowerTemplate');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ManpowerTemplate');
    }

    public function update(AuthUser $authUser, ManpowerTemplate $manpowerTemplate): bool
    {
        return $authUser->can('Update:ManpowerTemplate');
    }

    public function delete(AuthUser $authUser, ManpowerTemplate $manpowerTemplate): bool
    {
        return $authUser->can('Delete:ManpowerTemplate');
    }

    public function restore(AuthUser $authUser, ManpowerTemplate $manpowerTemplate): bool
    {
        return $authUser->can('Restore:ManpowerTemplate');
    }

    public function forceDelete(AuthUser $authUser, ManpowerTemplate $manpowerTemplate): bool
    {
        return $authUser->can('ForceDelete:ManpowerTemplate');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ManpowerTemplate');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ManpowerTemplate');
    }

    public function replicate(AuthUser $authUser, ManpowerTemplate $manpowerTemplate): bool
    {
        return $authUser->can('Replicate:ManpowerTemplate');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ManpowerTemplate');
    }
}
