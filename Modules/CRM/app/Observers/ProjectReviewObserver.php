<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\ProjectReview;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Models\ProfitabilityAnalysis;

class ProjectReviewObserver
{
    /**
     * Handle the ProjectReview "creating" event.
     */
    public function creating(ProjectReview $projectReview): void
    {
        $year = date('Y');
        $shortYear = date('y');

        $latest = ProjectReview::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $projectReview->year = $year;
        $projectReview->sequence_number = $sequence;
        $projectReview->number = sprintf('GDPS/UB/PR-%03d/%s', $sequence, $shortYear);
    }

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
                $projectReview->general_information_id = GeneralInformation::query()
                    ->where('lead_id', $leadId)
                    ->latest('created_at')
                    ->first()?->id;
            }

            if (! $projectReview->profitability_analysis_id) {
                $projectReview->profitability_analysis_id = ProfitabilityAnalysis::query()
                    ->where('lead_id', $leadId)
                    ->latest('created_at')
                    ->first()?->id;
            }

            if (! $projectReview->proposal_id) {
                $projectReview->proposal_id = $projectReview->profitabilityAnalysis?->proposal_id
                    ?? Proposal::query()
                        ->where('lead_id', $leadId)
                        ->latest('created_at')
                        ->first()?->id;
            }
        }
    }
}
