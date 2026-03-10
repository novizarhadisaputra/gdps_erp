<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\CRM\Models\MinutesOfAgreement;

class MinutesOfAgreementPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MinutesOfAgreement');
    }

    public function view(AuthUser $authUser, MinutesOfAgreement $minutesOfAgreement): bool
    {
        return $authUser->can('View:MinutesOfAgreement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MinutesOfAgreement');
    }

    public function update(AuthUser $authUser, MinutesOfAgreement $minutesOfAgreement): bool
    {
        return $authUser->can('Update:MinutesOfAgreement');
    }

    public function delete(AuthUser $authUser, MinutesOfAgreement $minutesOfAgreement): bool
    {
        return $authUser->can('Delete:MinutesOfAgreement');
    }

    public function restore(AuthUser $authUser, MinutesOfAgreement $minutesOfAgreement): bool
    {
        return $authUser->can('Restore:MinutesOfAgreement');
    }

    public function forceDelete(AuthUser $authUser, MinutesOfAgreement $minutesOfAgreement): bool
    {
        return $authUser->can('ForceDelete:MinutesOfAgreement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MinutesOfAgreement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MinutesOfAgreement');
    }

    public function replicate(AuthUser $authUser, MinutesOfAgreement $minutesOfAgreement): bool
    {
        return $authUser->can('Replicate:MinutesOfAgreement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MinutesOfAgreement');
    }
}
