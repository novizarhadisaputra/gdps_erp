<?php

namespace Modules\CRM\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageProjectReviews;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Tests\TestCase;

class ProjectReviewAndApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_view_project_review_and_approval_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $lead = Lead::factory()->create([
            'status' => LeadStatus::Approach,
        ]);

        $gi = GeneralInformation::factory()->create([
            'lead_id' => $lead->id,
            'rr_status' => 'approved',
            'rr_document_number' => 'RR-TEST-001',
        ]);

        $pa = ProfitabilityAnalysis::factory()->create([
            'lead_id' => $lead->id,
            'general_information_id' => $gi->id,
            'number' => 'PA-TEST-001',
        ]);

        $this->assertTrue($lead->latestGeneralInformation()->exists());
        $this->assertTrue($lead->profitabilityAnalyses()->exists());

        Livewire::test(ManageProjectReviews::class, [
            'record' => $lead->id,
        ])
            ->assertStatus(200);
    }
}
