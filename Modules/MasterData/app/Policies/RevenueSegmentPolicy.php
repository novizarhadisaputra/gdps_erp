<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\RevenueSegment;

class RevenueSegmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RevenueSegment');
    }

    public function view(AuthUser $authUser, RevenueSegment $revenueSegment): bool
    {
        return $authUser->can('View:RevenueSegment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RevenueSegment');
    }

    public function update(AuthUser $authUser, RevenueSegment $revenueSegment): bool
    {
        return $authUser->can('Update:RevenueSegment');
    }

    public function delete(AuthUser $authUser, RevenueSegment $revenueSegment): bool
    {
        return $authUser->can('Delete:RevenueSegment');
    }

    public function restore(AuthUser $authUser, RevenueSegment $revenueSegment): bool
    {
        return $authUser->can('Restore:RevenueSegment');
    }

    public function forceDelete(AuthUser $authUser, RevenueSegment $revenueSegment): bool
    {
        return $authUser->can('ForceDelete:RevenueSegment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RevenueSegment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RevenueSegment');
    }

    public function replicate(AuthUser $authUser, RevenueSegment $revenueSegment): bool
    {
        return $authUser->can('Replicate:RevenueSegment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RevenueSegment');
    }
}
