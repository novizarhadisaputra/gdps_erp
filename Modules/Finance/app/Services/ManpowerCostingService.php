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
        ?string $contractTypeId = null,
        ?string $workSchemeId = null,
        string|RiskLevel $riskLevel = 'very_low',
        bool $isLaborIntensive = false,
        string $employeeType = 'ppu', // ppu or pbpu
        bool $billThrMonthly = true,
        bool $billCompensationMonthly = true,
        float $adminFeePercentage = 0.0,
        float $managementFeeFlat = 0.0,
        string $ptkpCode = 'TK/0'
    ): array {
        $fixedAllowances = 0.0;
        $nonFixedAllowances = 0.0;

        $workScheme = $workSchemeId ? \Modules\MasterData\Models\WorkScheme::find($workSchemeId) : null;
        $workingDays = $workScheme?->working_days ?? 21;

        foreach ($allowances as $allowance) {
            $val = (float) ($allowance['value'] ?? $allowance['amount'] ?? 0);
            $type = $allowance['type'] ?? 'nominal';

            // Determination of fixed/non-fixed
            // If the allowance comes from the new model, it might have an 'is_fixed' flag.
            $isFixed = $allowance['is_fixed'] ?? true;

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
                'admin_fee' => 0,
                'management_fee' => 0,
                'total_direct_cost' => $upah + $nonFixedAllowances,
                'total_cost_to_company' => $upah + $nonFixedAllowances,
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

        // BPJS Calculation
        $bpjsHealth = $this->calculateBpjsHealth($upah, $umk, $employeeType);
        $bpjsEmployment = $this->calculateBpjsEmployment($upah, $riskLevel, $isLaborIntensive, $employeeType);

        $thr = $billThrMonthly ? ($upah / 12) : 0;
        $compensation = $billCompensationMonthly ? ($upah / 12) : 0;

        // PPH21 Calculation (Gross for PPh 21 = Upah + NonFixed + BPJS Employer)
        $pph21 = $this->calculatePph21($upah + $nonFixedAllowances, $bpjsHealth, $bpjsEmployment, $ptkpCode);

        $totalDirectCost = $upah + $nonFixedAllowances + $bpjsHealth['employer_total'] + $bpjsEmployment['employer_total'] + $thr + $compensation + $pph21['total'];

        $adminFee = $totalDirectCost * ($adminFeePercentage / 100);
        $totalCostToCompany = $totalDirectCost + $adminFee + $managementFeeFlat;

        return [
            'umk' => $umk,
            'upah' => $upah,
            'working_days' => $workingDays,
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
            'admin_fee' => $adminFee,
            'management_fee' => $managementFeeFlat,
            'total_direct_cost' => $totalDirectCost,
            'total_cost_to_company' => $totalCostToCompany,
            // Helpers for UI display
            'total_allowances' => $nonFixedAllowances,
            'bpjs_total' => $bpjsHealth['employer_total'] + $bpjsEmployment['employer_total'],
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

    protected function calculatePph21(float $grossIncomeValue, array $bpjsHealth, array $bpjsEmployment, ?string $ptkpCode = 'TK/0'): array
    {
        // 2024 TER Method (PMK 168/2023)
        // Gross income for PPh 21 includes employer-paid BPJS (JKN, JKK, JKM, JP)
        // Note: JP is included according to some interpretations, but mostly JKN, JKK, JKM are standard.
        // Based on spreadsheet formula, they include all employer BPJS.

        $employerBpjs = $bpjsHealth['employer_total'] + $bpjsEmployment['employer_total'];
        $grossTaxBase = $grossIncomeValue + $employerBpjs;

        // 1. Get PTKP and Category
        $ptkp = \Modules\MasterData\Models\PtkpConfig::where('code', $ptkpCode)->first();
        $category = $ptkp?->tax_category ?? 'A';

        // 2. Find TER rate from database
        $terRate = \Modules\MasterData\Models\TaxRateTer::where('category', $category)
            ->where('min_gross', '<=', $grossTaxBase)
            ->where(function ($query) use ($grossTaxBase) {
                $query->where('max_gross', '>=', $grossTaxBase)
                    ->orWhereNull('max_gross');
            })
            ->first();

        $rateValue = $terRate?->rate ?? 0;
        $monthlyTax = $grossTaxBase * ($rateValue / 100);

        return [
            'total' => $monthlyTax,
            'rate' => $rateValue / 100,
            'taxable_income' => $grossTaxBase,
            'category' => $category,
            'ptkp_code' => $ptkpCode,
            'gross_tax_base' => $grossTaxBase,
        ];
    }
}
