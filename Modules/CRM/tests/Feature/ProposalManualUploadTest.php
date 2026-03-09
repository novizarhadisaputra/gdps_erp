<?php

namespace Modules\CRM\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_can_manually_upload_proposal(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->create([
            'status' => LeadStatus::Approach,
            'estimated_amount' => 1000000,
        ]);

        $file = UploadedFile::fake()->create('proposal.pdf', 100);

        Livewire::actingAs($user)
            ->test(ManageProposals::class, [
                'record' => $lead->id,
            ])
            ->callTableAction('manualUpload', data: [
                'file' => [$file->store('temp-manual-uploads', 'local')],
                'amount' => '1.500.000',
                'submission_date' => now()->format('Y-m-d'),
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('proposals', [
            'lead_id' => $lead->id,
            'amount' => 1500000,
            'status' => ProposalStatus::Draft->value,
            'is_manual' => true,
        ]);

        $proposal = Proposal::where('lead_id', $lead->id)->first();
        $this->assertNotNull($proposal);
        /** @var \Modules\CRM\Models\Proposal $proposal */
        $this->assertEquals(1, $proposal->getMedia('final_proposal')->count());

        $lead->refresh();
        $this->assertEquals(LeadStatus::Proposal, $lead->status);
    }
}
