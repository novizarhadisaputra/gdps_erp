<?php

namespace Modules\Finance\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Modules\Finance\Enums\ProfitabilityAnalysisMonthlyStatus;
use Modules\Finance\Observers\ProfitabilityAnalysisMonthlyObserver;

#[ObservedBy(ProfitabilityAnalysisMonthlyObserver::class)]
class ProfitabilityAnalysisMonthly extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    protected $table = 'profitability_analysis_monthlies';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'profitability_analysis_id',
        'target_revenue',
        'forecast_revenue',
        'actual_revenue',
        'month',
        'year',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'target_revenue' => 'decimal:2',
            'forecast_revenue' => 'decimal:2',
            'actual_revenue' => 'decimal:2',
            'year' => 'integer',
            'status' => ProfitabilityAnalysisMonthlyStatus::class,
        ];
    }

    public function profitabilityAnalysis(): BelongsTo
    {
        return $this->belongsTo(ProfitabilityAnalysis::class);
    }

    public function weeklies(): HasMany
    {
        return $this->hasMany(ProfitabilityAnalysisWeekly::class, 'profitability_analysis_monthly_id');
    }
}
