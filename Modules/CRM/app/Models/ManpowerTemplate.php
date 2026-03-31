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

    public function getCostSimulation(): array
    {
        $service = app(\Modules\Finance\Services\ManpowerCostingService::class);
        $totalTemplateCost = 0;
        $rows = [];

        $areaId = $this->project_area_id;

        if (! $areaId) {
            return ['rows' => [], 'total' => 0];
        }

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

            // Allowances are defined per-item (per-project), not from JobPosition master data
            $allowances = $item->allowances ?? [];

            $riskLevel = $item->risk_level ?? 'very_low';
            $isLaborIntensive = (bool) ($item->is_labor_intensive ?? false);
            $employeeType = $item->employee_type ?? 'ppu';
            $billThr = (bool) ($item->bill_thr_monthly ?? true);
            $billComp = (bool) ($item->bill_compensation_monthly ?? true);
            $includeNonFixed = (bool) ($item->include_non_fixed_in_accruals ?? false);
            $extraCosts = $item->extra_costs ?? [];

            $basicSalary = (float) ($item->basic_salary ?? 0);

            $res = $service->calculate(
                basicSalary: $basicSalary,
                allowances: $allowances,
                projectAreaId: $areaId,
                year: (int) date('Y'),
                workSchemeId: $this->work_scheme_id,
                riskLevel: $riskLevel,
                isLaborIntensive: $isLaborIntensive,
                employeeType: $employeeType,
                billThrMonthly: $billThr,
                billCompensationMonthly: $billComp,
                includeNonFixedInAccruals: $includeNonFixed,
                extraCosts: $extraCosts
            );

            $unitCost = $res['total_direct_cost'];
            $lineTotal = $unitCost * $qty;
            $totalTemplateCost += $lineTotal;

            $res['job_position_id'] = $jpId;
            $res['job_position_name'] = $jp->name;
            $res['job_position_code'] = $jp->code;
            $res['qty'] = $qty;
            $res['basic_salary'] = $basicSalary;
            $res['unit_cost'] = $unitCost;
            $res['line_total'] = $lineTotal;

            $rows[] = $res;
        }

        return [
            'rows' => $rows,
            'total' => $totalTemplateCost,
        ];
    }
}
