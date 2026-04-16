<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Finance\Observers\ProfitabilityAnalysisActualObserver;

#[ObservedBy(ProfitabilityAnalysisActualObserver::class)]
class ProfitabilityAnalysisActual extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'profitability_analysis_id',
        'month',
        'year',
        'actual_revenue',
        'actual_cost',
        'actual_details',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'actual_details' => 'array',
        ];
    }

    public function profitabilityAnalysis(): BelongsTo
    {
        return $this->belongsTo(ProfitabilityAnalysis::class);
    }

    public function weeklyUpdates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProfitabilityAnalysisUpdate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
