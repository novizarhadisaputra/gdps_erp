<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Filament\Resources\Proposals\Pages\ListProposals;
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
        $user = User::factory()->create();
        $this->actingAs($user);

        $proposal = Proposal::factory()->create([
            'proposal_number' => 'PROP-001',
        ]);

        Livewire::test(ListProposals::class)
            ->assertCanSeeTableRecords([$proposal])
            ->assertSee('PROP-001');
    }
}
