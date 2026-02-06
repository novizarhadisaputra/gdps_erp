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
        'amount',
        'proposal_number',
        'project_code',
    ];

    public function salesPlan(): BelongsTo
    {
        return $this->belongsTo(SalesPlan::class);
    }
}
