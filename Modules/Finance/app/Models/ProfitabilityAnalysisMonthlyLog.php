<?php

namespace Modules\Finance\Models;

use App\Models\User;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfitabilityAnalysisMonthlyLog extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    protected $table = 'profitability_analysis_monthly_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'profitability_analysis_monthly_id',
        'user_id',
        'field_name',
        'old_value',
        'new_value',
        'delta',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'old_value' => 'decimal:2',
            'new_value' => 'decimal:2',
            'delta' => 'decimal:2',
        ];
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
