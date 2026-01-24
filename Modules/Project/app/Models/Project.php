<?php

namespace Modules\Project\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Project\Database\Factories\ProjectFactory;

class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'status',
        'client_id',
        'contract_id',
        'project_number',
        'work_scheme_id',
        'product_cluster_id',
        'tax_id',
        'payment_term_id',
        'project_type_id',
        'billing_option_id',
        'oprep_id',
        'ams_id',
        'project_area_id',
        'start_date',
        'end_date',
        'proposal_id',
        'profitability_analysis_id',
    ];

    public function proposal(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\CRM\Models\Proposal::class);
    }

    public function profitabilityAnalysis(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\Finance\Models\ProfitabilityAnalysis::class);
    }

    protected function casts(): array
    {
        return [];
    }

    public function information(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProjectInformation::class);
    }

    protected static function newFactory(): \Modules\Project\Database\Factories\ProjectFactory
    {
        return \Modules\Project\Database\Factories\ProjectFactory::new();
    }

    public function contract(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\CRM\Models\Contract::class);
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

    public function paymentTerm(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\PaymentTerm::class);
    }

    public function projectType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\ProjectType::class);
    }

    public function billingOption(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\BillingOption::class);
    }

    public function oprep(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\Employee::class, 'oprep_id');
    }

    public function ams(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\Employee::class, 'ams_id');
    }
}
