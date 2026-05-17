<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\CRM\Observers\ManpowerTemplateObserver;
use Modules\MasterData\Models\ProjectArea;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(ManpowerTemplateObserver::class)]
class ManpowerTemplate extends Model implements HasMedia
{
    use HasFactory, HasUuids;
    use HasModuleSchema;
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('source_file')
            ->useDisk('s3')
            ->singleFile()
            ->acceptsMimeTypes([
                'application/pdf',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
            ]);
    }

    protected static function newFactory(): \Modules\CRM\Database\Factories\ManpowerTemplateFactory
    {
        return \Modules\CRM\Database\Factories\ManpowerTemplateFactory::new();
    }

    protected $fillable = [
        'lead_id',
        'project_area_id',
        'work_scheme_id',
        'code',
        'name',
        'description',
        'is_active',
        'is_imported',
        'import_source_id',
        'sequence_number',
        'year',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_imported' => 'boolean',
    ];

    public function workScheme(): BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\WorkScheme::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ManpowerTemplateItem::class);
    }

    public function clusters(): HasMany
    {
        return $this->hasMany(ManpowerTemplateCluster::class, 'manpower_template_id');
    }

    public function getCostSimulation(): array
    {
        $service = app(\Modules\Finance\Services\ManpowerCostingService::class);
        $totalTemplateCost = 0;
        $rows = [];

        $areaId = $this->project_area_id;

        if (! $areaId) {
            return ['rows' => [], 'total' => 0];
        }

        // We loop through clusters then items to match the nested structure
        foreach ($this->clusters as $cluster) {
            foreach ($cluster->items as $item) {
                $jpId = $item->job_position_id ?? null;
                $qty = (int) ($item->quantity ?? 0);

                if (! $jpId || $qty <= 0) {
                    continue;
                }

                $jp = \Modules\MasterData\Models\JobPosition::find($jpId);
                if (! $jp) {
                    continue;
                }

                $allowances = $item->allowances ?? [];
                $riskLevel = $item->risk_level ?? 'very_low';
                $employeeType = $item->employee_type ?? 'ppu';

                // Inherit or override cluster policies
                $jknCategory = $item->jkn_category ?? $cluster->jkn_category ?? 'PPU';
                $thrMethod = $item->thr_billing_method ?? $cluster->thr_billing_method ?? 'monthly_accrual';
                $compMethod = $item->compensation_billing_method ?? $cluster->compensation_billing_method ?? 'monthly_accrual';

                $basicSalary = (float) ($item->basic_salary ?? 0);

                $res = $service->calculate(
                    basicSalary: $basicSalary,
                    allowances: $allowances,
                    projectAreaId: $areaId,
                    year: (int) date('Y'),
                    workSchemeId: $this->work_scheme_id,
                    workPatternId: $item->work_pattern_id,
                    riskLevel: $riskLevel,
                    employeeType: $employeeType,
                    jknCategory: $jknCategory,
                    thrBillingMethod: $thrMethod,
                    compensationBillingMethod: $compMethod,
                    thrBasisId: $item->thr_basis_id,
                    compensationBasisId: $item->compensation_basis_id,
                    bpjsBasisId: $item->bpjs_basis_id,
                    billThrMonthly: (bool) ($item->bill_thr_monthly ?? true),
                    billCompensationMonthly: (bool) ($item->bill_compensation_monthly ?? true),
                    includeNonFixedInAccruals: (bool) ($item->include_non_fixed_in_accruals ?? false),
                    extraCosts: $item->extra_costs ?? [],
                    ptkpCode: $item->ptkp_status ?? 'TK/0',
                    isBpjsActive: (bool) ($item->is_bpjs_active ?? true),
                    useTerMethod: (bool) ($item->use_ter_method ?? true),
                    borneByCompany: [
                        'tax' => (bool) ($item->is_tax_borne_by_company ?? false),
                        'jkn' => (bool) ($item->is_employee_jkn_borne_by_company ?? false),
                        'jkk' => (bool) ($item->is_employee_jkk_borne_by_company ?? false),
                        'jkm' => (bool) ($item->is_employee_jkm_borne_by_company ?? false),
                        'jht' => (bool) ($item->is_employee_jht_borne_by_company ?? false),
                        'jp' => (bool) ($item->is_employee_jp_borne_by_company ?? false),
                    ],
                    contractTypeId: $item->contract_type_id
                );

                // Apply Future Scaling Factor if defined
                $scale = 1 + ((float) ($item->future_adjustment_rate ?? 0) / 100);
                $unitCost = $res['total_direct_cost'] * $scale;
                $lineTotal = $unitCost * $qty;
                $totalTemplateCost += $lineTotal;

                $res['cluster_name'] = $cluster->productCluster?->name ?? 'Unassigned';
                $res['job_position_id'] = $jpId;
                $res['job_position_name'] = $jp->name;
                $res['job_position_code'] = $jp->code;
                $res['qty'] = $qty;
                $res['basic_salary'] = $basicSalary;
                $res['scaling_rate'] = (float) ($item->future_adjustment_rate ?? 0);
                $res['ptkp_status'] = $item->ptkp_status ?? 'TK/0';
                $res['unit_cost'] = $unitCost;
                $res['line_total'] = $lineTotal;

                $rows[] = $res;
            }
        }

        return [
            'rows' => $rows,
            'total' => $totalTemplateCost,
        ];
    }
}
