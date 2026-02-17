<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesPlanMonthly extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'sales_plan_id',
        'year',
        'month',
        'budget_amount',
        'forecast_amount',
        'actual_amount',
        'proposal_number',
        'project_code',
    ];

    protected $casts = [
        'budget_amount' => 'decimal:2',
        'forecast_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
    ];

    public function salesPlan(): BelongsTo
    {
        return $this->belongsTo(SalesPlan::class);
    }
}
