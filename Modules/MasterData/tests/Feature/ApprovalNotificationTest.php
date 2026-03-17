<?php

namespace Modules\MasterData\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Modules\CRM\Models\Proposal;
use Modules\MasterData\Models\ApprovalRule;
use Modules\MasterData\Notifications\ApprovalRequiredNotification;
use Modules\MasterData\Services\SignatureService;
use Tests\TestCase;

class ApprovalNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_notification_to_eligible_approvers()
    {
        Notification::fake();

        // 1. Setup Approver with a specific role
        $role = \App\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $approver = User::factory()->create();
        $approver->assignRole($role);

        // 2. Setup Approval Rule for Proposal
        ApprovalRule::create([
            'resource_type' => Proposal::class,
            'approver_type' => 'Role',
            'approver_role' => [$role->id],
            'signature_type' => 'approval',
            'order' => 1,
            'is_active' => true,
        ]);

        // 3. Setup Lead and Proposal
        $lead = \Modules\CRM\Models\Lead::factory()->create();
        $proposal = Proposal::factory()->create([
            'lead_id' => $lead->id,
            'status' => \Modules\CRM\Enums\ProposalStatus::Draft,
        ]);

        // 4. Trigger Notification via SignatureService
        $service = app(SignatureService::class);
        $service->notifyNextApprovers($proposal);

        // 5. Assert Notification was sent
        Notification::assertSentTo(
            [$approver],
            ApprovalRequiredNotification::class,
            function ($notification, $channels) use ($proposal, $approver) {
                return in_array('database', $channels) &&
                       $notification->toDatabase($approver)['record_id'] === $proposal->id;
            }
        );
    }
}
