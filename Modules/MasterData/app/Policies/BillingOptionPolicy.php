<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\BillingOption;

class BillingOptionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BillingOption');
    }

    public function view(AuthUser $authUser, BillingOption $billingOption): bool
    {
        return $authUser->can('View:BillingOption');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BillingOption');
    }

    public function update(AuthUser $authUser, BillingOption $billingOption): bool
    {
        return $authUser->can('Update:BillingOption');
    }

    public function delete(AuthUser $authUser, BillingOption $billingOption): bool
    {
        return $authUser->can('Delete:BillingOption');
    }

    public function restore(AuthUser $authUser, BillingOption $billingOption): bool
    {
        return $authUser->can('Restore:BillingOption');
    }

    public function forceDelete(AuthUser $authUser, BillingOption $billingOption): bool
    {
        return $authUser->can('ForceDelete:BillingOption');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BillingOption');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BillingOption');
    }

    public function replicate(AuthUser $authUser, BillingOption $billingOption): bool
    {
        return $authUser->can('Replicate:BillingOption');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BillingOption');
    }
}
