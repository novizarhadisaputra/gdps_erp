<?php

namespace Modules\Finance\Services;

use Modules\MasterData\Enums\RiskLevel;
use Modules\MasterData\Models\HealthConfig;
use Modules\MasterData\Models\JhtConfig;
use Modules\MasterData\Models\JkkConfig;
use Modules\MasterData\Models\JkmConfig;
use Modules\MasterData\Models\JpConfig;
use Modules\MasterData\Models\RegencyMinimumWage;
use Modules\MasterData\Models\WorkScheme;

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

        $workScheme = $workSchemeId ? WorkScheme::find($workSchemeId) : null;
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
        $cacheKey = "Health-{$employeeType}";
        if (isset(self::$bpjsCache[$cacheKey])) {
            $config = self::$bpjsCache[$cacheKey];
        } else {
            $config = HealthConfig::where('employee_type', $employeeType)->where('is_active', true)->first();
            self::$bpjsCache[$cacheKey] = $config;
        }

        if (! $config) {
            return ['employer_total' => 0, 'employee_total' => 0, 'base' => 0];
        }

        if (in_array($employeeType, ['pbpu', 'pbi'])) {
            return [
                'employer_total' => (float) $config->employer_nominal,
                'employee_total' => (float) $config->employee_nominal,
                'base' => (float) ($config->employer_nominal + $config->employee_nominal),
            ];
        }

        // PPU Flow
        $base = $upah;
        if ($base < $umk && $config->floor_type === 'umk') {
            $base = $umk;
        }

        if ($config->cap_nominal > 0 && $base > $config->cap_nominal) {
            $base = (float) $config->cap_nominal;
        }

        return [
            'employer_total' => $base * (float) $config->employer_rate,
            'employee_total' => $base * (float) $config->employee_rate,
            'base' => $base,
        ];
    }

    protected function calculateBpjsEmployment(float $upah, string $riskLevel, bool $isLaborIntensive, string $employeeType = 'ppu'): array
    {
        $details = [];
        $employerTotal = 0;
        $employeeTotal = 0;

        // --- 1. JKK --- //
        $jkkConfig = JkkConfig::where('employee_type', $employeeType)
            ->when($employeeType === 'ppu', fn ($q) => $q->where('risk_level', $riskLevel))
            ->where('is_active', true)
            ->first();

        if ($jkkConfig) {
            $employer = 0;
            $employee = 0;
            $base = $upah;

            if ($employeeType === 'pbpu' && $jkkConfig->has_tier) {
                // Tier Lookup
                $tier = $jkkConfig->tiers()
                    ->where('min_income', '<=', $upah)
                    ->where(function ($q) use ($upah) {
                        $q->whereNull('max_income')
                            ->orWhere('max_income', '>=', $upah);
                    })->first();

                if ($tier) {
                    $employer = (float) $tier->employer_nominal;
                    $employee = (float) $tier->employee_nominal;
                }
            } else {
                // Percentage based
                $empRate = (float) $jkkConfig->employer_rate;
                if ($isLaborIntensive && $employeeType === 'ppu') {
                    $empRate = $empRate * 0.5; // Example exception
                }
                $employer = $base * $empRate;
                $employee = $base * (float) $jkkConfig->employee_rate;
            }

            $details['jkk'] = ['employer' => $employer, 'employee' => $employee, 'base' => $base];
            $employerTotal += $employer;
            $employeeTotal += $employee;
        }

        // --- 2. JKM --- //
        $jkmConfig = JkmConfig::where('employee_type', $employeeType)->where('is_active', true)->first();
        if ($jkmConfig) {
            $employer = 0;
            $employee = 0;
            $base = $upah;

            if ($employeeType === 'pbpu') {
                $employer = (float) $jkmConfig->employer_nominal;
                $employee = (float) $jkmConfig->employee_nominal;
            } else {
                $employer = $base * (float) $jkmConfig->employer_rate;
                $employee = $base * (float) $jkmConfig->employee_rate;
            }

            $details['jkm'] = ['employer' => $employer, 'employee' => $employee, 'base' => $base];
            $employerTotal += $employer;
            $employeeTotal += $employee;
        }

        // --- 3. JHT --- //
        $jhtConfig = JhtConfig::where('employee_type', $employeeType)->where('is_active', true)->first();
        if ($jhtConfig) {
            $employer = 0;
            $employee = 0;
            $base = $upah;

            if ($employeeType === 'pbpu' && $jhtConfig->has_tier) {
                $tier = $jhtConfig->tiers()
                    ->where('min_income', '<=', $upah)
                    ->where(function ($q) use ($upah) {
                        $q->whereNull('max_income')
                            ->orWhere('max_income', '>=', $upah);
                    })->first();

                if ($tier) {
                    $employer = (float) $tier->employer_nominal;
                    $employee = (float) $tier->employee_nominal;
                }
            } else {
                $employer = $base * (float) $jhtConfig->employer_rate;
                $employee = $base * (float) $jhtConfig->employee_rate;
            }

            $details['jht'] = ['employer' => $employer, 'employee' => $employee, 'base' => $base];
            $employerTotal += $employer;
            $employeeTotal += $employee;
        }

        // --- 4. JP --- //
        $jpConfig = JpConfig::where('employee_type', $employeeType)->where('is_active', true)->first();
        if ($jpConfig) {
            $base = $upah;
            if ($jpConfig->cap_nominal > 0 && $base > $jpConfig->cap_nominal) {
                $base = (float) $jpConfig->cap_nominal;
            }

            $employer = $base * (float) $jpConfig->employer_rate;
            $employee = $base * (float) $jpConfig->employee_rate;

            $details['jp'] = ['employer' => $employer, 'employee' => $employee, 'base' => $base];
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
        $employerBpjs = $bpjsHealth['employer_total'] + $bpjsEmployment['employer_total'];
        $grossTaxBase = $grossIncomeValue + $employerBpjs;

        $ptkp = \Modules\MasterData\Models\PtkpConfig::where('code', $ptkpCode)->first();
        $category = $ptkp?->tax_category ?? 'A';

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
