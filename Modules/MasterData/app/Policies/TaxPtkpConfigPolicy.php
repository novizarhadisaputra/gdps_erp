<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\TaxPtkpConfig;

class TaxPtkpConfigPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TaxPtkpConfig');
    }

    public function view(AuthUser $authUser, TaxPtkpConfig $taxPtkpConfig): bool
    {
        return $authUser->can('View:TaxPtkpConfig');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TaxPtkpConfig');
    }

    public function update(AuthUser $authUser, TaxPtkpConfig $taxPtkpConfig): bool
    {
        return $authUser->can('Update:TaxPtkpConfig');
    }

    public function delete(AuthUser $authUser, TaxPtkpConfig $taxPtkpConfig): bool
    {
        return $authUser->can('Delete:TaxPtkpConfig');
    }

    public function restore(AuthUser $authUser, TaxPtkpConfig $taxPtkpConfig): bool
    {
        return $authUser->can('Restore:TaxPtkpConfig');
    }

    public function forceDelete(AuthUser $authUser, TaxPtkpConfig $taxPtkpConfig): bool
    {
        return $authUser->can('ForceDelete:TaxPtkpConfig');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TaxPtkpConfig');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TaxPtkpConfig');
    }

    public function replicate(AuthUser $authUser, TaxPtkpConfig $taxPtkpConfig): bool
    {
        return $authUser->can('Replicate:TaxPtkpConfig');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TaxPtkpConfig');
    }
}
