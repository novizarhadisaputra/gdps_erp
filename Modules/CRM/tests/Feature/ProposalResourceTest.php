<?php

namespace Modules\CRM\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages\ListProposals;
use Modules\CRM\Models\Proposal;
use Tests\TestCase;

class ProposalResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_proposals(): void
    {
        $lead = \Modules\CRM\Models\Lead::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $proposal = Proposal::factory()->create([
            'lead_id' => $lead->id,
            'proposal_number' => 'PROP-001',
        ]);

        Livewire::test(ListProposals::class, [
            'lead' => $lead->id,
            'record' => $lead->id,
        ])
            ->assertCanSeeTableRecords([$proposal])
            ->assertSee('PROP-001');
    }
}
