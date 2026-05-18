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

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ManpowerTemplateItem::class);
    }

    public function getCostSimulation(): array
    {
        $service = app(\Modules\Finance\Services\ManpowerCostingService::class);
        $totalTemplateCost = 0;
        $rows = [];

        // Loop through flat items directly
        foreach ($this->items as $item) {
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

            $jknCategory = $item->jkn_category ?? 'PPU';
            $thrMethod = $item->thr_billing_method ?? 'monthly_accrual';
            $compMethod = $item->compensation_billing_method ?? 'monthly_accrual';

            $basicSalary = (float) ($item->basic_salary ?? 0);

            $res = $service->calculate(
                basicSalary: $basicSalary,
                allowances: $allowances,
                projectAreaId: $item->project_area_id,
                year: $this->year ?? (int) date('Y'),
                workSchemeId: $item->work_scheme_id,
                riskLevel: $riskLevel,
                employeeType: $employeeType,
                jknCategory: $jknCategory,
                thrBillingMethod: $thrMethod,
                compensationBillingMethod: $compMethod,
                thrBasisId: $item->thr_basis_id,
                compensationBasisId: $item->compensation_basis_id,
                bpjsKesehatanBasisId: $item->bpjs_kesehatan_basis_id,
                bpjsKetenagakerjaanBasisId: $item->bpjs_ketenagakerjaan_basis_id,
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
                contractTypeId: $item->contract_type_id,
                taxObjectId: $item->tax_object_id,
                bpjsHealthConfigId: $item->bpjs_health_config_id,
                bpjsJkkConfigId: $item->bpjs_jkk_config_id,
                bpjsJkmConfigId: $item->bpjs_jkm_config_id,
                bpjsJhtConfigId: $item->bpjs_jht_config_id,
                bpjsJpConfigId: $item->bpjs_jp_config_id
            );

            // Apply Future Scaling Factor if defined
            $scale = 1 + ((float) ($item->future_adjustment_rate ?? 0) / 100);
            $unitCost = $res['total_direct_cost'] * $scale;
            $lineTotal = $unitCost * $qty;
            $totalTemplateCost += $lineTotal;

            $res['cluster_name'] = $item->productCluster?->name ?? 'Unassigned';
            $res['job_position_id'] = $jpId;
            $res['job_position_name'] = $jp->name;
            $res['job_position_code'] = $jp->code;
            $res['qty'] = $qty;
            $res['quantity'] = $qty;
            $res['basic_salary'] = $basicSalary;
            $res['scaling_rate'] = (float) ($item->future_adjustment_rate ?? 0);
            $res['ptkp_status'] = $item->ptkp_status ?? 'TK/0';
            $res['unit_cost'] = $unitCost;
            $res['unit_direct_cost'] = $unitCost;
            $res['line_total'] = $lineTotal;
            $res['subtotal_direct_cost'] = $lineTotal;

            $rows[] = $res;
        }

        return [
            'rows' => $rows,
            'total' => $totalTemplateCost,
        ];
    }
}
