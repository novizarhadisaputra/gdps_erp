<?php

namespace Modules\CRM\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageProposals;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Proposal;
use Tests\TestCase;

class ProposalManualUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Gate::before(fn () => true);
        Storage::fake('local');
        Storage::fake('s3');
    }

    public function test_can_book_proposal_code(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->create([
            'status' => LeadStatus::Approach,
            'estimated_amount' => 1000000,
        ]);

        Livewire::actingAs($user)
            ->test(ManageProposals::class, [
                'record' => $lead->id,
            ])
            ->callAction(
                \Filament\Actions\Testing\TestAction::make('bookingCode')->table()
            )
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Proposal::class, [
            'lead_id' => $lead->id,
            'amount' => 1000000,
            'status' => ProposalStatus::Draft->value,
            'is_manual' => true,
        ]);

        /** @var Proposal $proposal */
        $proposal = Proposal::where('lead_id', $lead->id)->first();
        $this->assertNotNull($proposal);
        $this->assertStringContainsString('GDPS/UB/PROP-', $proposal->number);
        $this->assertEquals(0, $proposal->getMedia('final_proposal')->count());

        $lead->refresh();
        $this->assertEquals(LeadStatus::Proposal, $lead->status);
    }
}
