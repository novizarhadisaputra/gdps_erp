<?php

namespace Modules\MasterData\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Proposal;
use Modules\MasterData\Notifications\ApprovalSignedNotification;
use Modules\MasterData\Services\SignatureService;
use Tests\TestCase;

class SignatureNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_notification_to_owner_when_signed()
    {
        Notification::fake();

        // Arrange
        $owner = User::factory()->create();
        $approver = User::factory()->create();

        $lead = Lead::factory()->create(['user_id' => $owner->id]);
        $proposal = Proposal::factory()->create([
            'lead_id' => $lead->id,
            'customer_id' => $lead->customer_id,
        ]);

        $service = app(SignatureService::class);

        // Act
        $service->notifyOwnerOnSignature($proposal, $approver, 'approval');

        // Assert
        Notification::assertSentTo(
            $owner,
            ApprovalSignedNotification::class,
            function ($notification, $channels) use ($owner, $proposal, $approver) {
                return $notification->toDatabase($owner)['record_id'] === $proposal->id &&
                       str_contains($notification->toDatabase($owner)['body'], $approver->name);
            }
        );
    }

    public function test_it_does_not_notify_owner_if_they_are_the_approver()
    {
        Notification::fake();

        // Arrange
        $owner = User::factory()->create();

        $lead = Lead::factory()->create(['user_id' => $owner->id]);
        $proposal = Proposal::factory()->create([
            'lead_id' => $lead->id,
            'customer_id' => $lead->customer_id,
        ]);

        $service = app(SignatureService::class);

        // Act
        $service->notifyOwnerOnSignature($proposal, $owner, 'approval');

        // Assert
        Notification::assertNotSentTo($owner, ApprovalSignedNotification::class);
    }
}
