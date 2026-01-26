<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Finance\Database\Factories\ProfitabilityAnalysisFactory;

class ProfitabilityAnalysis extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Modules\Finance\Database\Factories\ProfitabilityAnalysisFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'proposal_id',
        'client_id',
        'work_scheme_id',
        'product_cluster_id',
        'tax_id',
        'project_area_id',
        'revenue_per_month',
        'direct_cost',
        'management_fee',
        'margin_percentage',
        'manpower_details',
        'material_details',
        'project_number',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'revenue_per_month' => 'decimal:2',
            'direct_cost' => 'decimal:2',
            'management_fee' => 'decimal:2',
            'margin_percentage' => 'decimal:2',
            'manpower_details' => 'array',
            'material_details' => 'array',
            'project_number' => 'integer',
        ];
    }

    public function proposal(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\CRM\Models\Proposal::class);
    }

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\Client::class);
    }

    public function workScheme(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\WorkScheme::class);
    }

    public function productCluster(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\ProductCluster::class);
    }

    public function tax(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\Tax::class);
    }

    public function projectArea(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\ProjectArea::class);
    }
}
