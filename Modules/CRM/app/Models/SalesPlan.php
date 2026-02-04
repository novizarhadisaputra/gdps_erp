<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesPlan extends Model
{
    use HasUuids;

    protected $fillable = [
        'lead_id',
        'project_type',
        'industry',
        'estimated_value',
        'start_date',
        'end_date',
        'confidence_level',
        'revenue_distribution_planning',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'estimated_value' => 'decimal:2',
        'revenue_distribution_planning' => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
