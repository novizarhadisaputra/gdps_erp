<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\PartnerFeeType;

class PartnerFeeTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PartnerFeeType');
    }

    public function view(AuthUser $authUser, PartnerFeeType $partnerFeeType): bool
    {
        return $authUser->can('View:PartnerFeeType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PartnerFeeType');
    }

    public function update(AuthUser $authUser, PartnerFeeType $partnerFeeType): bool
    {
        return $authUser->can('Update:PartnerFeeType');
    }

    public function delete(AuthUser $authUser, PartnerFeeType $partnerFeeType): bool
    {
        return $authUser->can('Delete:PartnerFeeType');
    }

    public function restore(AuthUser $authUser, PartnerFeeType $partnerFeeType): bool
    {
        return $authUser->can('Restore:PartnerFeeType');
    }

    public function forceDelete(AuthUser $authUser, PartnerFeeType $partnerFeeType): bool
    {
        return $authUser->can('ForceDelete:PartnerFeeType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PartnerFeeType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PartnerFeeType');
    }

    public function replicate(AuthUser $authUser, PartnerFeeType $partnerFeeType): bool
    {
        return $authUser->can('Replicate:PartnerFeeType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PartnerFeeType');
    }
}
