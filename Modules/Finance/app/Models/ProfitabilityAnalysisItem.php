<?php

namespace Modules\Finance\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProfitabilityAnalysisItem extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'profitability_analysis_id',
        'costable_id',
        'costable_type',
        'quantity',
        'unit_cost_price',
        'markup_percentage',
        'depreciation_months',
        'total_monthly_cost',
        'total_monthly_sale',
        'cost_breakdown',
        'duration_months',
        'import_source_id',
        'direct_cost_category_id',
        'ptkp_config_id',
        'calculation_type',
        'percentage_basis',
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
            'direct_cost_category_id' => 'string',
            'calculation_type' => 'string',
            'percentage_basis' => 'string',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DirectCostCategory::class, 'direct_cost_category_id');
    }

    public function profitabilityAnalysis(): BelongsTo
    {
        return $this->belongsTo(ProfitabilityAnalysis::class);
    }

    public function costable(): MorphTo
    {
        return $this->morphTo();
    }

    public function ptkpConfig(): BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\PtkpConfig::class, 'ptkp_config_id');
    }
}
