<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\CRM\Models\SalesPlan;

class SalesPlanPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SalesPlan');
    }

    public function view(AuthUser $authUser, SalesPlan $salesPlan): bool
    {
        return $authUser->can('View:SalesPlan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SalesPlan');
    }

    public function update(AuthUser $authUser, SalesPlan $salesPlan): bool
    {
        return $authUser->can('Update:SalesPlan');
    }

    public function delete(AuthUser $authUser, SalesPlan $salesPlan): bool
    {
        return $authUser->can('Delete:SalesPlan');
    }

    public function restore(AuthUser $authUser, SalesPlan $salesPlan): bool
    {
        return $authUser->can('Restore:SalesPlan');
    }

    public function forceDelete(AuthUser $authUser, SalesPlan $salesPlan): bool
    {
        return $authUser->can('ForceDelete:SalesPlan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SalesPlan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SalesPlan');
    }

    public function replicate(AuthUser $authUser, SalesPlan $salesPlan): bool
    {
        return $authUser->can('Replicate:SalesPlan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SalesPlan');
    }
}
