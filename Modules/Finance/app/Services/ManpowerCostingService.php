<?php

namespace Modules\Finance\Services;

use Modules\MasterData\Enums\RiskLevel;
use Modules\MasterData\Models\BpjsConfig;
use Modules\MasterData\Models\RegencyMinimumWage;

class ManpowerCostingService
{
    protected static array $umkCache = [];

    protected static array $bpjsCache = [];

    /**
     * Calculate manpower cost breakdown.
     */
    public function calculate(
        float $basicSalary,
        array $allowances,
        ?string $projectAreaId,
        ?int $year,
        string|RiskLevel $riskLevel = 'very_low',
        bool $isLaborIntensive = false,
        string $employeeType = 'ppu', // ppu or pbpu
        bool $billThrMonthly = true,
        bool $billCompensationMonthly = true
    ): array {
        $fixedAllowances = 0.0;
        $nonFixedAllowances = 0.0;

        foreach ($allowances as $allowance) {
            $val = (float) ($allowance['value'] ?? $allowance['amount'] ?? 0);
            $type = $allowance['type'] ?? 'nominal';
            $isFixed = (bool) ($allowance['is_fixed'] ?? true);

            $amount = $type === 'percentage' ? ($basicSalary * ($val / 100)) : $val;

            if ($isFixed) {
                $fixedAllowances += $amount;
            } else {
                $nonFixedAllowances += $amount;
            }
        }

        $upah = $basicSalary + $fixedAllowances;

        if (! $projectAreaId || ! $year) {
            return [
                'umk' => 0,
                'upah' => $upah,
                'allowances' => ['fixed' => $fixedAllowances, 'non_fixed' => $nonFixedAllowances],
                'bpjs_health' => ['employer_total' => 0, 'employee_total' => 0, 'base' => 0],
                'bpjs_employment' => ['employer_total' => 0, 'employee_total' => 0],
                'pph21' => ['total' => 0, 'rate' => 0, 'taxable_income' => 0],
                'accruals' => ['thr' => 0, 'compensation' => 0],
                'total_direct_cost' => $upah + $nonFixedAllowances,
                'total_allowances' => $nonFixedAllowances,
                'bpjs_total' => 0,
                'thr_compensation' => 0,
            ];
        }

        if ($riskLevel instanceof RiskLevel) {
            $riskLevel = $riskLevel->value;
        }

        $cacheKey = "{$projectAreaId}-{$year}";
        if (isset(self::$umkCache[$cacheKey])) {
            $umk = self::$umkCache[$cacheKey];
        } else {
            $umk = RegencyMinimumWage::where('project_area_id', $projectAreaId)
                ->where('year', $year)
                ->first()?->amount ?? 0;
            self::$umkCache[$cacheKey] = (float) $umk;
        }

        // BPJS Base is usually Upah, but floored by UMK and capped by specified limits
        $bpjsHealth = $this->calculateBpjsHealth($upah, $umk, $employeeType);
        $bpjsEmployment = $this->calculateBpjsEmployment($upah, $riskLevel, $isLaborIntensive, $employeeType);

        $thr = $billThrMonthly ? ($upah / 12) : 0;
        $compensation = $billCompensationMonthly ? ($upah / 12) : 0;

        // PPH21 Calculation (Simplified for now)
        $pph21 = $this->calculatePph21($upah + $nonFixedAllowances + $bpjsHealth['employer_total'] + $bpjsEmployment['employer_total']);

        $totalDirectCost = $upah + $nonFixedAllowances + $bpjsHealth['employer_total'] + $bpjsEmployment['employer_total'] + $thr + $compensation + $pph21['total'];

        return [
            'umk' => $umk,
            'upah' => $upah,
            'allowances' => [
                'fixed' => $fixedAllowances,
                'non_fixed' => $nonFixedAllowances,
            ],
            'bpjs_health' => $bpjsHealth,
            'bpjs_employment' => $bpjsEmployment,
            'pph21' => $pph21,
            'accruals' => [
                'thr' => $thr,
                'compensation' => $compensation,
            ],
            'total_direct_cost' => $totalDirectCost,
            // Helpers for UI display
            'total_allowances' => $nonFixedAllowances,
            'bpjs_total' => $bpjsHealth['employer_total'] + $bpjsEmployment['employer_total'] + $pph21['total'], // PPh usually grouped with tax/deductions
            'thr_compensation' => $thr + $compensation,
        ];
    }

    protected function calculateBpjsHealth(float $upah, float $umk, string $employeeType = 'ppu'): array
    {
        if ($employeeType === 'pbpu') {
            return [
                'employer_total' => 150000, // Fixed for Class 1 as example
                'employee_total' => 0,
                'base' => 150000,
            ];
        }

        $cacheKey = 'Health-active';
        if (isset(self::$bpjsCache[$cacheKey])) {
            $config = self::$bpjsCache[$cacheKey];
        } else {
            $config = BpjsConfig::where('category', 'Health')->where('is_active', true)->first();
            self::$bpjsCache[$cacheKey] = $config;
        }

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

    protected function calculateBpjsEmployment(float $upah, string $riskLevel, bool $isLaborIntensive, string $employeeType = 'ppu'): array
    {
        if ($employeeType === 'pbpu') {
            // PBPU rates are simpler, often a flat rate or fixed % of income
            // For now, let's just use a simple %
            $incomeFlat = $upah;

            return [
                'details' => [
                    'jkk' => ['employer' => $incomeFlat * 0.01, 'employee' => 0, 'base' => $incomeFlat],
                    'jkm' => ['employer' => 6800, 'employee' => 0, 'base' => 6800],
                ],
                'employer_total' => ($incomeFlat * 0.01) + 6800,
                'employee_total' => 0,
            ];
        }

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

    protected function calculatePph21(float $grossIncome): array
    {
        // Very simplified PPH21 logic for demonstration
        // 5% rate for anything above 5M/month after relief
        $relief = 4500000; // PTKP monthly approx
        $taxable = max(0, $grossIncome - $relief);
        $tax = $taxable * 0.05;

        return [
            'total' => $tax,
            'rate' => 0.05,
            'taxable_income' => $taxable,
        ];
    }
}
