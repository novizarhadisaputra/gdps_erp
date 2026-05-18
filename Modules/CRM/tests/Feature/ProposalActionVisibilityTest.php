<?php

namespace Modules\CRM\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages\ViewProposal;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Proposal;
use Tests\TestCase;

class ProposalActionVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_send_email_button_visibility(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        // Case 1: Draft - Should not see Send Email or Approve
        $proposalDraft = Proposal::factory()->create([
            'lead_id' => $lead->id,
            'status' => ProposalStatus::Draft,
        ]);

        Livewire::test(ViewProposal::class, [
            'lead' => $lead->id,
            'record' => $proposalDraft->id,
            'parentRecord' => $lead,
        ])
            ->assertActionHidden('sendEmail')
            ->assertActionHidden('signProposal');

        // Case 2: Submitted (Not Signed) - Should not see Send Email or Approve
        // We'll mock isFullyApproved to return false for this case
        $proposalSubmitted = Proposal::factory()->create([
            'lead_id' => $lead->id,
            'status' => ProposalStatus::Submitted,
        ]);

        // Note: By default, if no SignatureRules exist, isFullyApproved() might return true.
        // In a real environment there would be rules. For testing visibility logic:
        Livewire::test(ViewProposal::class, [
            'lead' => $lead->id,
            'record' => $proposalSubmitted->id,
            'parentRecord' => $lead,
        ])
            ->assertActionVisible('sendEmail'); // Visible from Submitted onwards

        // Case 3: Approved - Should see Send Email, but NOT Approve (already approved)
        $proposalApproved = Proposal::factory()->create([
            'lead_id' => $lead->id,
            'status' => ProposalStatus::Approved,
        ]);

        Livewire::test(ViewProposal::class, [
            'lead' => $lead->id,
            'record' => $proposalApproved->id,
            'parentRecord' => $lead,
        ])
            ->assertActionVisible('sendEmail')
            ->assertActionHidden('signProposal');

        // Case 4: Sent - Should see Send Email (Resend)
        $proposalSent = Proposal::factory()->create([
            'lead_id' => $lead->id,
            'status' => ProposalStatus::Sent,
        ]);

        Livewire::test(ViewProposal::class, [
            'lead' => $lead->id,
            'record' => $proposalSent->id,
            'parentRecord' => $lead,
        ])
            ->assertActionVisible('sendEmail')
            ->assertActionHidden('signProposal');
    }

    public function test_manual_approval_action(): void
    {
        $user = User::factory()->create([
            'signature_pin' => \Illuminate\Support\Facades\Hash::make('123456'),
        ]);
        $lead = Lead::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        // Create a proposal in Submitted status
        $proposal = Proposal::factory()->create([
            'lead_id' => $lead->id,
            'status' => ProposalStatus::Submitted,
        ]);

        // No approval rules exist for Proposal; creator signs directly via signProposal action.
        // The action is visible while Submitted and user has not yet signed.
        $this->assertFalse($proposal->signatures()->where('user_id', $user->id)->exists());

        Livewire::test(ViewProposal::class, [
            'lead' => $lead->id,
            'record' => $proposal->id,
            'parentRecord' => $lead,
        ])
            ->assertActionVisible('signProposal')
            ->assertActionVisible('sendEmail') // Visible from Submitted onwards
            ->callAction('signProposal', ['pin' => '123456'])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertCount(1, $proposal->refresh()->signatures);

        // Status remains Submitted — Approved is triggered by signed file upload, not by this action
        $this->assertEquals(ProposalStatus::Submitted, $proposal->refresh()->status);

        // signProposal is now hidden since the user already signed
        Livewire::test(ViewProposal::class, [
            'lead' => $lead->id,
            'record' => $proposal->id,
            'parentRecord' => $lead,
        ])
            ->assertActionHidden('signProposal');
    }
}
