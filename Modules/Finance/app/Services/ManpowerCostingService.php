<?php

namespace Modules\Finance\Services;

use Modules\MasterData\Models\BpjsConfig;
use Modules\MasterData\Models\RegencyMinimumWage;

class ManpowerCostingService
{
    /**
     * Calculate manpower cost breakdown.
     */
    public function calculate(
        float $basicSalary,
        array $allowances,
        string $projectAreaId,
        int $year,
        string $riskLevel = 'very_low',
        bool $isLaborIntensive = false
    ): array {
        $umk = RegencyMinimumWage::where('project_area_id', $projectAreaId)
            ->where('year', $year)
            ->first()?->amount ?? 0;

        $fixedAllowances = collect($allowances)
            ->filter(fn ($a) => ($a['type'] ?? '') === 'nominal' && ($a['is_fixed'] ?? true))
            ->sum(fn ($a) => $a['value'] ?? $a['amount'] ?? 0);

        $nonFixedAllowances = collect($allowances)
            ->filter(fn ($a) => ($a['type'] ?? '') === 'nominal' && ! ($a['is_fixed'] ?? true))
            ->sum(fn ($a) => $a['value'] ?? $a['amount'] ?? 0);

        $upah = $basicSalary + $fixedAllowances;

        // BPJS Base is usually Upah, but floored by UMK and capped by specified limits
        $bpjsHealth = $this->calculateBpjsHealth($upah, $umk);
        $bpjsEmployment = $this->calculateBpjsEmployment($upah, $riskLevel, $isLaborIntensive);

        $thr = $upah / 12;
        $compensation = $upah / 12;

        $totalDirectCost = $upah + $nonFixedAllowances + $bpjsHealth['employer_total'] + $bpjsEmployment['employer_total'] + $thr + $compensation;

        return [
            'umk' => $umk,
            'upah' => $upah,
            'allowances' => [
                'fixed' => $fixedAllowances,
                'non_fixed' => $nonFixedAllowances,
            ],
            'bpjs_health' => $bpjsHealth,
            'bpjs_employment' => $bpjsEmployment,
            'accruals' => [
                'thr' => $thr,
                'compensation' => $compensation,
            ],
            'total_direct_cost' => $totalDirectCost,
        ];
    }

    protected function calculateBpjsHealth(float $upah, float $umk): array
    {
        $config = BpjsConfig::where('category', 'Health')->where('is_active', true)->first();

        if (! $config) {
            return ['employer_total' => 0, 'employee_total' => 0];
        }

        $base = $upah;
        if ($base < $umk && $config->floor_type === 'umk') {
            $base = $umk;
        }

        if ($config->cap_type === 'nominal' && $base > $config->cap_nominal) {
            $base = $config->cap_nominal;
        }

        return [
            'employer_total' => $base * $config->employer_rate,
            'employee_total' => $base * $config->employee_rate,
            'base' => $base,
        ];
    }

    protected function calculateBpjsEmployment(float $upah, string $riskLevel, bool $isLaborIntensive): array
    {
        $categories = ['JKK', 'JKM', 'JHT', 'JP'];
        $details = [];
        $employerTotal = 0;
        $employeeTotal = 0;

        foreach ($categories as $category) {
            $query = BpjsConfig::where('category', $category)->where('is_active', true);

            if ($category === 'JKK') {
                $query->where('risk_level', $riskLevel);
            }

            $config = $query->first();

            if (! $config) {
                continue;
            }

            $base = $upah;
            if ($config->cap_type === 'nominal' && $base > $config->cap_nominal) {
                $base = $config->cap_nominal;
            }

            $empRate = (float) $config->employer_rate;
            if ($category === 'JKK' && $isLaborIntensive) {
                $empRate = $empRate * 0.5; // 50% reduction
            }

            $employer = $base * $empRate;
            $employee = $base * $config->employee_rate;

            $details[strtolower($category)] = [
                'employer' => $employer,
                'employee' => $employee,
                'base' => $base,
            ];

            $employerTotal += $employer;
            $employeeTotal += $employee;
        }

        return [
            'details' => $details,
            'employer_total' => $employerTotal,
            'employee_total' => $employeeTotal,
        ];
    }
}
