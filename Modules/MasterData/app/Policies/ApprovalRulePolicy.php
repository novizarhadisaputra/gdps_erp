<?php

declare(strict_types=1);

namespace Modules\MasterData\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\MasterData\Models\ApprovalRule;

class ApprovalRulePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ApprovalRule');
    }

    public function view(AuthUser $authUser, ApprovalRule $approvalRule): bool
    {
        return $authUser->can('View:ApprovalRule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ApprovalRule');
    }

    public function update(AuthUser $authUser, ApprovalRule $approvalRule): bool
    {
        return $authUser->can('Update:ApprovalRule');
    }

    public function delete(AuthUser $authUser, ApprovalRule $approvalRule): bool
    {
        return $authUser->can('Delete:ApprovalRule');
    }

    public function restore(AuthUser $authUser, ApprovalRule $approvalRule): bool
    {
        return $authUser->can('Restore:ApprovalRule');
    }

    public function forceDelete(AuthUser $authUser, ApprovalRule $approvalRule): bool
    {
        return $authUser->can('ForceDelete:ApprovalRule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ApprovalRule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ApprovalRule');
    }

    public function replicate(AuthUser $authUser, ApprovalRule $approvalRule): bool
    {
        return $authUser->can('Replicate:ApprovalRule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ApprovalRule');
    }
}
