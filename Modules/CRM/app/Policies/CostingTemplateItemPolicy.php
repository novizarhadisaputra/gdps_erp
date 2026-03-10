<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\CRM\Models\CostingTemplateItem;

class CostingTemplateItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CostingTemplateItem');
    }

    public function view(AuthUser $authUser, CostingTemplateItem $costingTemplateItem): bool
    {
        return $authUser->can('View:CostingTemplateItem');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CostingTemplateItem');
    }

    public function update(AuthUser $authUser, CostingTemplateItem $costingTemplateItem): bool
    {
        return $authUser->can('Update:CostingTemplateItem');
    }

    public function delete(AuthUser $authUser, CostingTemplateItem $costingTemplateItem): bool
    {
        return $authUser->can('Delete:CostingTemplateItem');
    }

    public function restore(AuthUser $authUser, CostingTemplateItem $costingTemplateItem): bool
    {
        return $authUser->can('Restore:CostingTemplateItem');
    }

    public function forceDelete(AuthUser $authUser, CostingTemplateItem $costingTemplateItem): bool
    {
        return $authUser->can('ForceDelete:CostingTemplateItem');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CostingTemplateItem');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CostingTemplateItem');
    }

    public function replicate(AuthUser $authUser, CostingTemplateItem $costingTemplateItem): bool
    {
        return $authUser->can('Replicate:CostingTemplateItem');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CostingTemplateItem');
    }
}
