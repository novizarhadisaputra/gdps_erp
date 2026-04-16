<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Finance\Observers\ProfitabilityAnalysisWeeklyObserver;

#[ObservedBy(ProfitabilityAnalysisWeeklyObserver::class)]
class ProfitabilityAnalysisWeekly extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'profitability_analysis_weeklies';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'profitability_analysis_id',
        'profitability_analysis_monthly_id',
        'projected_revenue',
        'achieved_revenue',
        'notes',
        'week_number',
        'month',
        'year',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'projected_revenue' => 'decimal:2',
            'achieved_revenue' => 'decimal:2',
            'week_number' => 'integer',
            'year' => 'integer',
        ];
    }

    public function profitabilityAnalysis(): BelongsTo
    {
        return $this->belongsTo(ProfitabilityAnalysis::class);
    }

    public function monthly(): BelongsTo
    {
        return $this->belongsTo(ProfitabilityAnalysisMonthly::class, 'profitability_analysis_monthly_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
