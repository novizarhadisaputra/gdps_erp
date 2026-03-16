<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\CRM\Database\Factories\ProjectReviewFactory;
use Modules\CRM\Observers\ProjectReviewObserver;
use Modules\Finance\Models\ProfitabilityAnalysis;

#[ObservedBy([ProjectReviewObserver::class])]
class ProjectReview extends Model
{
    use HasFactory, HasModuleSchema, HasUuids;

    protected $fillable = [
        'lead_id',
        'general_information_id',
        'profitability_analysis_id',
        'proposal_id',
        'status',
        'revision_number',
    ];

    protected static function newFactory(): ProjectReviewFactory
    {
        return ProjectReviewFactory::new();
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function generalInformation(): BelongsTo
    {
        return $this->belongsTo(GeneralInformation::class);
    }

    public function profitabilityAnalysis(): BelongsTo
    {
        return $this->belongsTo(ProfitabilityAnalysis::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }
}
