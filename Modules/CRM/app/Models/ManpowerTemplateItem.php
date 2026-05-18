<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\CRM\Observers\ManpowerTemplateItemObserver;
use Modules\MasterData\Models\BpjsBasisType;
use Modules\MasterData\Models\BpjsHealthConfig;
use Modules\MasterData\Models\BpjsJhtConfig;
use Modules\MasterData\Models\BpjsJkkConfig;
use Modules\MasterData\Models\BpjsJkmConfig;
use Modules\MasterData\Models\BpjsJpConfig;
use Modules\MasterData\Models\ContractType;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\TaxObject;
use Modules\MasterData\Models\ThrBasisType;
use Modules\MasterData\Models\WorkScheme;

#[ObservedBy(ManpowerTemplateItemObserver::class)]
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
        'product_cluster_id',
        'project_area_id',
        'work_scheme_id',
        'contract_type_id',
        'job_position_id',
        'tax_object_id',
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
        'bpjs_kesehatan_basis_id',
        'bpjs_ketenagakerjaan_basis_id',
        'bpjs_health_config_id',
        'bpjs_jkk_config_id',
        'bpjs_jkm_config_id',
        'bpjs_jht_config_id',
        'bpjs_jp_config_id',
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

    public function productCluster(): BelongsTo
    {
        return $this->belongsTo(ProductCluster::class);
    }

    public function workScheme(): BelongsTo
    {
        return $this->belongsTo(WorkScheme::class);
    }

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }

    public function thrBasis(): BelongsTo
    {
        return $this->belongsTo(ThrBasisType::class, 'thr_basis_id');
    }

    public function compensationBasis(): BelongsTo
    {
        return $this->belongsTo(ThrBasisType::class, 'compensation_basis_id');
    }

    public function bpjsKesehatanBasis(): BelongsTo
    {
        return $this->belongsTo(BpjsBasisType::class, 'bpjs_kesehatan_basis_id');
    }

    public function bpjsKetenagakerjaanBasis(): BelongsTo
    {
        return $this->belongsTo(BpjsBasisType::class, 'bpjs_ketenagakerjaan_basis_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ManpowerTemplate::class, 'manpower_template_id');
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class);
    }

    public function taxObject(): BelongsTo
    {
        return $this->belongsTo(TaxObject::class);
    }

    public function bpjsHealthConfig(): BelongsTo
    {
        return $this->belongsTo(BpjsHealthConfig::class, 'bpjs_health_config_id');
    }

    public function bpjsJkkConfig(): BelongsTo
    {
        return $this->belongsTo(BpjsJkkConfig::class, 'bpjs_jkk_config_id');
    }

    public function bpjsJkmConfig(): BelongsTo
    {
        return $this->belongsTo(BpjsJkmConfig::class, 'bpjs_jkm_config_id');
    }

    public function bpjsJhtConfig(): BelongsTo
    {
        return $this->belongsTo(BpjsJhtConfig::class, 'bpjs_jht_config_id');
    }

    public function bpjsJpConfig(): BelongsTo
    {
        return $this->belongsTo(BpjsJpConfig::class, 'bpjs_jp_config_id');
    }
}
