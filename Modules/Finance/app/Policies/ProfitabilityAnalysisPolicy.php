<?php

declare(strict_types=1);

namespace Modules\Finance\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Finance\Models\ProfitabilityAnalysis;

class ProfitabilityAnalysisPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProfitabilityAnalysis');
    }

    public function view(AuthUser $authUser, ProfitabilityAnalysis $profitabilityAnalysis): bool
    {
        return $authUser->can('View:ProfitabilityAnalysis');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProfitabilityAnalysis');
    }

    public function update(AuthUser $authUser, ProfitabilityAnalysis $profitabilityAnalysis): bool
    {
        return $authUser->can('Update:ProfitabilityAnalysis');
    }

    public function delete(AuthUser $authUser, ProfitabilityAnalysis $profitabilityAnalysis): bool
    {
        return $authUser->can('Delete:ProfitabilityAnalysis');
    }

    public function restore(AuthUser $authUser, ProfitabilityAnalysis $profitabilityAnalysis): bool
    {
        return $authUser->can('Restore:ProfitabilityAnalysis');
    }

    public function forceDelete(AuthUser $authUser, ProfitabilityAnalysis $profitabilityAnalysis): bool
    {
        return $authUser->can('ForceDelete:ProfitabilityAnalysis');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProfitabilityAnalysis');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProfitabilityAnalysis');
    }

    public function replicate(AuthUser $authUser, ProfitabilityAnalysis $profitabilityAnalysis): bool
    {
        return $authUser->can('Replicate:ProfitabilityAnalysis');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProfitabilityAnalysis');
    }
}
