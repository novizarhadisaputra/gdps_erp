<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\BenefitType;

class BenefitTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BenefitType');
    }

    public function view(AuthUser $authUser, BenefitType $benefitType): bool
    {
        return $authUser->can('View:BenefitType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BenefitType');
    }

    public function update(AuthUser $authUser, BenefitType $benefitType): bool
    {
        return $authUser->can('Update:BenefitType');
    }

    public function delete(AuthUser $authUser, BenefitType $benefitType): bool
    {
        return $authUser->can('Delete:BenefitType');
    }

    public function restore(AuthUser $authUser, BenefitType $benefitType): bool
    {
        return $authUser->can('Restore:BenefitType');
    }

    public function forceDelete(AuthUser $authUser, BenefitType $benefitType): bool
    {
        return $authUser->can('ForceDelete:BenefitType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BenefitType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BenefitType');
    }

    public function replicate(AuthUser $authUser, BenefitType $benefitType): bool
    {
        return $authUser->can('Replicate:BenefitType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BenefitType');
    }
}
