<?php

declare(strict_types=1);

namespace Modules\Project\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Models\WorkCompletionReport;

class WorkCompletionReportPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return true;
    }

    public function view(AuthUser $authUser, WorkCompletionReport $report): bool
    {
        return true;
    }

    public function create(AuthUser $authUser): bool
    {
        return true;
    }

    public function update(AuthUser $authUser, WorkCompletionReport $report): bool
    {
        /** @var User $user */
        $user = $authUser;

        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Project members can only edit Draft reports
        if ($report->status !== WorkCompletionStatus::Draft) {
            return false;
        }

        $isMember = $report->project->members()
            ->whereHasMorph('memberable', '*', function ($query) use ($user) {
                $query->where('email', $user->email);
            })->exists();

        return $isMember;
    }

    public function delete(AuthUser $authUser, WorkCompletionReport $report): bool
    {
        return $authUser->hasRole('super_admin');
    }
}
