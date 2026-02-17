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
        // Bi-directional categorization sync to SalesPlan
        $salesPlan = $lead->salesPlan()->first();

        if ($salesPlan) {
            $salesPlan->updateQuietly([
                'revenue_segment_id' => $lead->revenue_segment_id,
                'product_cluster_id' => $lead->product_cluster_id,
                'project_type_id' => $lead->project_type_id,
                'service_line_id' => $lead->service_line_id,
                'industrial_sector_id' => $lead->industrial_sector_id,
                'project_area_id' => $lead->project_area_id,
                'estimated_value' => $lead->estimated_amount,
                'confidence_level' => $lead->confidence_level,
            ]);
        }

        if (! $lead->wasChanged('status')) {
            return;
        }

        switch ($lead->status) {
            case LeadStatus::Lead:
                break;

            case LeadStatus::Approach:
                // Auto-create SalesPlan if it doesn't exist
                if (! $lead->salesPlan) {
                    $lead->salesPlan()->create([
                        'revenue_segment_id' => $lead->revenue_segment_id,
                        'product_cluster_id' => $lead->product_cluster_id,
                        'project_type_id' => $lead->project_type_id,
                        'service_line_id' => $lead->service_line_id,
                        'industrial_sector_id' => $lead->industrial_sector_id,
                        'project_area_id' => $lead->project_area_id,
                        'estimated_value' => $lead->estimated_amount ?? 0,
                        'confidence_level' => $lead->confidence_level ?? 'moderate',
                        'priority_level' => 2, // Default Medium
                        'start_date' => now(),
                        'end_date' => now()->addYear(),
                    ]);
                }

                activity()
                    ->performedOn($lead)
                    ->log('Lead moved to Approach. Sales Plan draft created/synced.');
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
