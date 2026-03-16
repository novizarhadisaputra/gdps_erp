<?php

namespace Modules\CRM\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Enums\GeneralInformationStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageProjectReviews;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\ProjectReview;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Tests\TestCase;

class ProjectReviewNestedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_project_reviews_via_manage_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $lead = Lead::factory()->create();
        $projectReview = ProjectReview::factory()->create([
            'lead_id' => $lead->id,
        ]);

        // Mock the route for ManageRelatedRecords
        $route = new \Illuminate\Routing\Route(['GET', 'HEAD'], 'crm/leads/{record}/project-reviews', [
            'as' => 'filament.admin.crm.resources.leads.project-reviews',
        ]);
        $route->bind(request());
        $route->setParameter('record', (string) $lead->id);
        request()->setRouteResolver(fn () => $route);

        Livewire::test(ManageProjectReviews::class, [
            'record' => $lead->id,
        ])
            ->assertCanSeeTableRecords([$projectReview])
            ->assertStatus(200);
    }

    public function test_project_review_is_created_automatically_when_gi_is_submitted(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $lead = Lead::factory()->create();
        $gi = GeneralInformation::factory()->create([
            'lead_id' => $lead->id,
            'status' => GeneralInformationStatus::Draft,
        ]);

        $this->assertDatabaseMissing(ProjectReview::class, [
            'lead_id' => $lead->id,
            'general_information_id' => $gi->id,
        ]);

        // Submit GI
        $gi->update(['status' => GeneralInformationStatus::Submitted]);

        $this->assertDatabaseHas(ProjectReview::class, [
            'lead_id' => $lead->id,
            'general_information_id' => $gi->id,
            'status' => 'draft',
        ]);
    }

    public function test_project_review_links_pa_and_proposal_automatically(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $lead = Lead::factory()->create();
        $gi = GeneralInformation::factory()->create(['lead_id' => $lead->id]);

        // Submit GI to create ProjectReview
        $gi->update(['status' => GeneralInformationStatus::Submitted]);

        $projectReview = ProjectReview::where('lead_id', $lead->id)->first();
        $this->assertNull($projectReview->profitability_analysis_id);
        $this->assertNull($projectReview->proposal_id);

        // Create PA
        $pa = ProfitabilityAnalysis::factory()->create([
            'lead_id' => $lead->id,
            'general_information_id' => $gi->id,
        ]);

        $projectReview->refresh();
        $this->assertEquals($pa->id, $projectReview->profitability_analysis_id);

        // Create Proposal
        $proposal = Proposal::factory()->create([
            'lead_id' => $lead->id,
            'profitability_analysis_id' => $pa->id,
        ]);

        $projectReview->refresh();
        $this->assertEquals($proposal->id, $projectReview->proposal_id);
    }
}
