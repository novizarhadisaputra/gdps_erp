<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Actions\MoveToApproachAction;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ViewLead;
use Modules\CRM\Models\Lead;
use Tests\TestCase;

class LeadStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_move_lead_to_approach(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $lead = Lead::factory()->create([
            'status' => LeadStatus::Lead,
            'probability' => 0,
        ]);

        Livewire::test(ViewLead::class, ['record' => $lead->id])
            ->callAction(MoveToApproachAction::class)
            ->assertHasNoFormErrors();

        $lead->refresh();

        $this->assertEquals(LeadStatus::Approach, $lead->status);
        $this->assertEquals(10, $lead->probability);
    }
}
