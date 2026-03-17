<?php

namespace Modules\MasterData\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Modules\CRM\Models\Proposal;
use Modules\MasterData\Notifications\ApprovalRejectedNotification;
use Modules\MasterData\Services\SignatureService;
use Tests\TestCase;

class RejectionNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_notification_to_owner_on_rejection()
    {
        Notification::fake();

        // 1. Setup Owner
        $owner = User::factory()->create();

        // 2. Setup Lead and Proposal (owned by user)
        $lead = \Modules\CRM\Models\Lead::factory()->create(['user_id' => $owner->id]);
        $proposal = Proposal::factory()->create([
            'lead_id' => $lead->id,
            'status' => \Modules\CRM\Enums\ProposalStatus::Submitted,
        ]);

        // 3. Trigger Rejection Notification via SignatureService
        $service = app(SignatureService::class);
        $reason = 'Pricing is too high';
        $service->notifyOwnerOnRejection($proposal, $reason);

        // 4. Assert Notification was sent to owner
        Notification::assertSentTo(
            [$owner],
            ApprovalRejectedNotification::class,
            function ($notification, $channels) use ($proposal, $owner, $reason) {
                $data = $notification->toDatabase($owner);

                return in_array('database', $channels) &&
                       $data['record_id'] === $proposal->id &&
                       str_contains($data['body'], $reason);
            }
        );
    }
}
