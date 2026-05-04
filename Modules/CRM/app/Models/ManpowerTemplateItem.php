<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Models\JobPosition;

class ManpowerTemplateItem extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    protected static function newFactory(): \Modules\CRM\Database\Factories\ManpowerTemplateItemFactory
    {
        return \Modules\CRM\Database\Factories\ManpowerTemplateItemFactory::new();
    }

    protected $fillable = [
        'manpower_template_id',
        'manpower_template_cluster_id',
        'product_cluster_id',
        'work_pattern_id',
        'job_position_id',
        'quantity',
        'basic_salary',
        'notes',
        'risk_level',
        'employee_type',
        'is_labor_intensive',
        'bill_thr_monthly',
        'bill_compensation_monthly',
        'include_non_fixed_in_accruals',
        'extra_costs',
        'allowances',
        'future_adjustment_rate',
        'ptkp_status',
        'is_bpjs_active',
        'use_ter_method',
        'is_tax_borne_by_company',
        'is_employee_jkn_borne_by_company',
        'is_employee_jkk_borne_by_company',
        'is_employee_jkm_borne_by_company',
        'is_employee_jht_borne_by_company',
        'is_employee_jp_borne_by_company',
        'jkn_category',
        'thr_billing_method',
        'compensation_billing_method',
        'thr_basis_id',
        'compensation_basis_id',
        'bpjs_basis_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'basic_salary' => 'decimal:2',
            'is_labor_intensive' => 'boolean',
            'bill_thr_monthly' => 'boolean',
            'bill_compensation_monthly' => 'boolean',
            'include_non_fixed_in_accruals' => 'boolean',
            'extra_costs' => 'array',
            'allowances' => 'array',
            'future_adjustment_rate' => 'float',
            'is_bpjs_active' => 'boolean',
            'use_ter_method' => 'boolean',
            'is_tax_borne_by_company' => 'boolean',
            'is_employee_jkn_borne_by_company' => 'boolean',
            'is_employee_jkk_borne_by_company' => 'boolean',
            'is_employee_jkm_borne_by_company' => 'boolean',
            'is_employee_jht_borne_by_company' => 'boolean',
            'is_employee_jp_borne_by_company' => 'boolean',
        ];
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(ManpowerTemplateCluster::class, 'manpower_template_cluster_id');
    }

    public function productCluster(): BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\ProductCluster::class);
    }

    public function workPattern(): BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\WorkPattern::class);
    }

    public function thrBasis(): BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\ThrBasisType::class, 'thr_basis_id');
    }

    public function compensationBasis(): BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\ThrBasisType::class, 'compensation_basis_id');
    }

    public function bpjsBasis(): BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\BpjsBasisType::class, 'bpjs_basis_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ManpowerTemplate::class, 'manpower_template_id');
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }
}
