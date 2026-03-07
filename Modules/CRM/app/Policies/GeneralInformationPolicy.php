<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\CRM\Models\GeneralInformation;

class GeneralInformationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GeneralInformation');
    }

    public function view(AuthUser $authUser, GeneralInformation $generalInformation): bool
    {
        return $authUser->can('View:GeneralInformation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GeneralInformation');
    }

    public function update(AuthUser $authUser, GeneralInformation $generalInformation): bool
    {
        if ($generalInformation->isLocked()) {
            return false;
        }

        return $authUser->can('Update:GeneralInformation');
    }

    public function delete(AuthUser $authUser, GeneralInformation $generalInformation): bool
    {
        if ($generalInformation->isLocked()) {
            return false;
        }

        return $authUser->can('Delete:GeneralInformation');
    }

    public function restore(AuthUser $authUser, GeneralInformation $generalInformation): bool
    {
        return $authUser->can('Restore:GeneralInformation');
    }

    public function forceDelete(AuthUser $authUser, GeneralInformation $generalInformation): bool
    {
        return $authUser->can('ForceDelete:GeneralInformation');
    }
}
