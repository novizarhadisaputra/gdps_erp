<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\TaxScheme;

class TaxSchemePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TaxScheme');
    }

    public function view(AuthUser $authUser, TaxScheme $taxScheme): bool
    {
        return $authUser->can('View:TaxScheme');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TaxScheme');
    }

    public function update(AuthUser $authUser, TaxScheme $taxScheme): bool
    {
        return $authUser->can('Update:TaxScheme');
    }

    public function delete(AuthUser $authUser, TaxScheme $taxScheme): bool
    {
        return $authUser->can('Delete:TaxScheme');
    }

    public function restore(AuthUser $authUser, TaxScheme $taxScheme): bool
    {
        return $authUser->can('Restore:TaxScheme');
    }

    public function forceDelete(AuthUser $authUser, TaxScheme $taxScheme): bool
    {
        return $authUser->can('ForceDelete:TaxScheme');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TaxScheme');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TaxScheme');
    }

    public function replicate(AuthUser $authUser, TaxScheme $taxScheme): bool
    {
        return $authUser->can('Replicate:TaxScheme');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TaxScheme');
    }
}
