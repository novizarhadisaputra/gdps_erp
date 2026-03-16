<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\ProjectReview;

class ProjectReviewObserver
{
    /**
     * Handle the ProjectReview "saving" event.
     */
    public function saving(ProjectReview $projectReview): void
    {
        $this->linkLatestDocuments($projectReview);
    }

    /**
     * Link latest documents if not specifically set.
     */
    protected function linkLatestDocuments(ProjectReview $projectReview): void
    {
        if ($projectReview->lead_id) {
            $leadId = $projectReview->lead_id;

            if (! $projectReview->general_information_id) {
                $projectReview->general_information_id = \Modules\CRM\Models\GeneralInformation::query()
                    ->where('lead_id', $leadId)
                    ->latest('created_at')
                    ->first()?->id;
            }

            if (! $projectReview->profitability_analysis_id) {
                $projectReview->profitability_analysis_id = \Modules\Finance\Models\ProfitabilityAnalysis::query()
                    ->where('lead_id', $leadId)
                    ->latest('created_at')
                    ->first()?->id;
            }

            if (! $projectReview->proposal_id) {
                $projectReview->proposal_id = $projectReview->profitabilityAnalysis?->proposal_id
                    ?? \Modules\CRM\Models\Proposal::query()
                        ->where('lead_id', $leadId)
                        ->latest('created_at')
                        ->first()?->id;
            }
        }
    }
}
