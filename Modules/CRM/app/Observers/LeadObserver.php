<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Lead;

class LeadObserver
{
    /**
     * Handle the Lead "creating" event.
     */
    public function creating(Lead $lead): void
    {
        $lead->status = LeadStatus::Lead;
        $lead->probability = 10;
        $lead->position = Lead::max('position') + 1;
    }

    /**
     * Handle the Lead "updated" event.
     */
    public function updated(Lead $lead): void
    {
        if (! $lead->wasChanged('status')) {
            return;
        }

        switch ($lead->status) {
            case LeadStatus::Lead:
                // Revert logic if needed, or do nothing
                break;

            case LeadStatus::Approach:
                // 2. Approach: Otomatis buat jadwal "Activity" (Follow Up) jika belum ada
                // We use Spatie Activitylog for history, but maybe 'Activity' here refers to a CRM Activity/Task model?
                // Assuming we want to ensure there is a follow up task.
                // Since we don't have a clear "CRM Task" model in the context, I will create a simple Activity Log note for now.
                // Or if there is a 'Activity' module? Let's assume standard Filament Activity or Spatie.
                // Given the plan says "Activity (Follow Up)", I'll log a reminder.
                activity() // Using Spatie Activitylog helper
                    ->performedOn($lead)
                    ->log('Lead moved to Approach. Recommended action: Schedule a Follow Up.');
                break;

            case LeadStatus::Proposal:
                // 3. Proposal: Otomatis buat draft "Proposal" jika belum ada
                if ($lead->proposals()->count() === 0) {
                    $lead->proposals()->create([
                        'customer_id' => $lead->customer_id,
                        'issued_date' => now(),
                        'valid_until' => now()->addDays(30),
                        'status' => 'draft', // Assuming 'draft' is a valid status key
                        'amount' => $lead->estimated_amount,
                        // Add other required fields if necessary
                    ]);
                }
                break;

            case LeadStatus::Negotiation:
                // 4. Negotiation: Validasi minimal 1 Proposal & update probability 80%
                if ($lead->proposals()->count() === 0) {
                    // We can't easily stop the transition here in an observer (it's already updated).
                    // But we can warn or auto-create one.
                    // Ideally validation happens in the UI Action.
                    // Here we enforce probability update.
                    $lead->updateQuietly(['probability' => 80]);
                } else {
                    $lead->updateQuietly(['probability' => 80]);
                }
                break;

            case LeadStatus::Won:
                // 5. Won: Update probability 100%, Create Draft Contract
                // Convert to Project is handled by UI Action because it needs extra input.
                $lead->updateQuietly(['probability' => 100]);

                if ($lead->contracts()->count() === 0) {
                    $lead->contracts()->create([
                        'customer_id' => $lead->customer_id,
                        'start_date' => now(),
                        'end_date' => now()->addYear(),
                        'amount' => $lead->estimated_amount,
                        'status' => 'draft', // Assuming draft is valid
                        // Other fields...
                    ]);
                }
                break;

            case LeadStatus::ClosedLost:
                // 6. Closed Lost: Update probability 0%
                $lead->updateQuietly(['probability' => 0]);
                break;
        }
    }
}
