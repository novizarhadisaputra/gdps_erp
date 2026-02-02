<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Models\Item;

class ProfitabilityAnalysisItem extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'profitability_analysis_id',
        'item_id',
        'quantity',
        'unit_cost_price',
        'markup_percentage',
        'depreciation_months',
        'total_monthly_cost',
        'total_monthly_sale',
        'cost_breakdown',
        'duration_months',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_cost_price' => 'decimal:2',
            'markup_percentage' => 'decimal:2',
            'depreciation_months' => 'integer',
            'total_monthly_cost' => 'decimal:2',
            'total_monthly_sale' => 'decimal:2',
            'cost_breakdown' => 'array',
            'duration_months' => 'integer',
        ];
    }

    public function profitabilityAnalysis(): BelongsTo
    {
        return $this->belongsTo(ProfitabilityAnalysis::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
