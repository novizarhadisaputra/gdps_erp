<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\CRM\Models\CostingTemplate;

class CostingTemplatePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CostingTemplate');
    }

    public function view(AuthUser $authUser, CostingTemplate $costingTemplate): bool
    {
        return $authUser->can('View:CostingTemplate');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CostingTemplate');
    }

    public function update(AuthUser $authUser, CostingTemplate $costingTemplate): bool
    {
        return $authUser->can('Update:CostingTemplate');
    }

    public function delete(AuthUser $authUser, CostingTemplate $costingTemplate): bool
    {
        return $authUser->can('Delete:CostingTemplate');
    }

    public function restore(AuthUser $authUser, CostingTemplate $costingTemplate): bool
    {
        return $authUser->can('Restore:CostingTemplate');
    }

    public function forceDelete(AuthUser $authUser, CostingTemplate $costingTemplate): bool
    {
        return $authUser->can('ForceDelete:CostingTemplate');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CostingTemplate');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CostingTemplate');
    }

    public function replicate(AuthUser $authUser, CostingTemplate $costingTemplate): bool
    {
        return $authUser->can('Replicate:CostingTemplate');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CostingTemplate');
    }
}
