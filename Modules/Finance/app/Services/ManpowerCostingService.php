<?php

namespace Modules\Finance\Services;

use Modules\MasterData\Enums\RiskLevel;
use Modules\MasterData\Models\HealthConfig;
use Modules\MasterData\Models\JhtConfig;
use Modules\MasterData\Models\JkkConfig;
use Modules\MasterData\Models\JkmConfig;
use Modules\MasterData\Models\JpConfig;
use Modules\MasterData\Models\PtkpConfig;
use Modules\MasterData\Models\RegencyMinimumWage;
use Modules\MasterData\Models\TaxRateTer;
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
        ?string $workSchemeId = null,
        string|RiskLevel $riskLevel = 'very_low',
        bool $isLaborIntensive = false,
        string $employeeType = 'ppu', // ppu or pbpu
        bool $billThrMonthly = true,
        bool $billCompensationMonthly = true,
        bool $includeNonFixedInAccruals = false,
        array $extraCosts = [], // Monthly flat costs (Equipments/Trainings)
        float $adminFeePercentage = 0.0,
        float $managementFeeFlat = 0.0,
        string $ptkpCode = 'TK/0',
        bool $isBpjsActive = true
    ): array {
        $cacheKey = "{$projectAreaId}-{$year}";

        if (isset(self::$umkCache[$cacheKey])) {
            $umk = self::$umkCache[$cacheKey];
        } else {
            $umk = 0;
            if (filled($projectAreaId) && filled($year)) {
                $umk = RegencyMinimumWage::where('project_area_id', $projectAreaId)
                    ->where('year', $year)
                    ->first()?->amount ?? 0;
            }
            self::$umkCache[$cacheKey] = (float) $umk;
        }

        // Fallback to UMK if basic salary is not provided
        if ($basicSalary <= 0 && $umk > 0) {
            $basicSalary = $umk;
        }

        $fixedAllowances = 0.0;
        $nonFixedAllowances = 0.0;

        $workScheme = $workSchemeId ? WorkScheme::find($workSchemeId) : null;
        $workingDays = $workScheme?->working_days ?? 21;

        if ($riskLevel instanceof RiskLevel) {
            $riskLevel = $riskLevel->value;
        }

        foreach ($allowances as $allowance) {
            $val = (float) ($allowance['value'] ?? $allowance['amount'] ?? 0);
            $type = $allowance['type'] ?? 'nominal';
            $isFixed = $allowance['is_fixed'] ?? true;

            $amount = $type === 'percentage' ? ($basicSalary * ($val / 100)) : $val;

            if ($isFixed) {
                $fixedAllowances += $amount;
            } else {
                $nonFixedAllowances += $amount;
            }
        }

        $upah = $basicSalary + $fixedAllowances;
        $totalMonthlySalary = $upah + $nonFixedAllowances;

        // BPJS Calculation
        if ($isBpjsActive) {
            $bpjsHealth = $this->calculateBpjsHealth($upah, $umk, $employeeType);
            $bpjsEmployment = $this->calculateBpjsEmployment($upah, $riskLevel, $isLaborIntensive, $employeeType);
        } else {
            $bpjsHealth = ['employer' => 0, 'employee' => 0, 'employer_total' => 0, 'base' => 0];
            $bpjsEmployment = ['employer_total' => 0, 'employee_total' => 0, 'details' => []];
        }

        // Accrual Basis (THR/Comp may include non-fixed depending on position logic)
        $accrualBasis = $includeNonFixedInAccruals ? $totalMonthlySalary : $upah;
        $thr = $billThrMonthly ? ($accrualBasis / 12) : 0;
        $compensation = $billCompensationMonthly ? ($accrualBasis / 12) : 0;

        // Extra monthly costs which are flat (Equipment/Training)
        $extraCostsTotal = array_reduce($extraCosts, fn ($carry, $item) => $carry + (float) ($item['amount'] ?? 0), 0);

        // PPH21 Calculation
        $pph21 = $this->calculatePph21($totalMonthlySalary + $thr + $compensation, $bpjsHealth, $bpjsEmployment, $ptkpCode);

        // Management Fee Calculation
        // Management fee base usually includes all costs plus PPh 21 (if borne by company)
        $costBaseForFee = $totalMonthlySalary + $thr + $compensation + $bpjsHealth['employer_total'] + $bpjsEmployment['employer_total'] + $pph21['total'] + $extraCostsTotal;
        $adminFee = $costBaseForFee * ($adminFeePercentage / 100);

        $totalDirectCost = $costBaseForFee;
        $totalCostToCompany = $totalDirectCost + $adminFee + $managementFeeFlat;

        return [
            'upah' => $upah,
            'total_monthly_salary' => $totalMonthlySalary,
            'umk' => $umk,
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
                'basis' => $accrualBasis,
            ],
            'extra_costs' => $extraCosts,
            'extra_costs_total' => $extraCostsTotal,
            'admin_fee' => $adminFee,
            'management_fee' => $managementFeeFlat,
            'total_direct_cost' => $totalDirectCost,
            'total_cost_to_company' => $totalCostToCompany,
            'total_allowances' => $nonFixedAllowances,
            'bpjs_total' => $bpjsHealth['employer_total'] + $bpjsEmployment['employer_total'],
        ];
    }

    protected function calculateBpjsHealth(float $upah, float $umk, string $employeeType): array
    {
        /** @var HealthConfig|null $config */
        $config = HealthConfig::where('employee_type', $employeeType)->where('is_active', true)->first();

        if (! $config) {
            return ['employer' => 0, 'employee' => 0, 'employer_total' => 0];
        }

        $base = $upah;
        if ($employeeType === 'ppu') {
            $cap = (float) ($config->cap_nominal ?? 999999999);
            $floor = 0;
            if ($config->floor_type === 'umk') {
                $floor = $umk;
            }
            $base = max(min($upah, $cap), $floor);
        }

        $employer = $base * (float) $config->employer_rate;
        $employee = $base * (float) $config->employee_rate;

        return [
            'employer_rate' => $config->employer_rate,
            'employee_rate' => $config->employee_rate,
            'base' => $base,
            'employer' => $employer,
            'employee' => $employee,
            'employer_total' => $employer,
        ];
    }

    protected function calculateBpjsEmployment(float $upah, string $riskLevel, bool $isLaborIntensive, string $employeeType): array
    {
        $employerTotal = 0;
        $employeeTotal = 0;
        $details = [];

        // --- 1. JKK --- //
        /** @var JkkConfig|null $jkkConfig */
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
        /** @var JkmConfig|null $jkmConfig */
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
        /** @var JhtConfig|null $jhtConfig */
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
        /** @var JpConfig|null $jpConfig */
        $jpConfig = JpConfig::where('employee_type', $employeeType)->where('is_active', true)->first();
        if ($jpConfig) {
            $base = min($upah, (float) ($jpConfig->cap_nominal ?? 999999999));
            $employer = $base * (float) $jpConfig->employer_rate;
            $employee = $base * (float) $jpConfig->employee_rate;

            $details['jp'] = ['employer' => $employer, 'employee' => $employee, 'base' => $base];
            $employerTotal += $employer;
            $employeeTotal += $employee;
        }

        return [
            'employer_total' => $employerTotal,
            'employee_total' => $employeeTotal,
            'details' => $details,
        ];
    }

    protected function calculatePph21(float $taxableIncome, array $bpjsHealth, array $bpjsEmployment, string $ptkpCode = 'TK/0'): array
    {
        // 1. Get Category from PTKP Code (A, B, or C)
        /** @var PtkpConfig|null $ptkp */
        $ptkp = PtkpConfig::where('code', $ptkpCode)->first();
        $category = $ptkp?->tax_category ?? 'A';

        // JKK, JKM, JP, and Health (Employer portion) are tax objects for the employee in this spreadsheet template.
        // JHT (Employer portion) is NOT a tax object.
        $jkkEmployer = (float) ($bpjsEmployment['details']['jkk']['employer'] ?? 0);
        $jkmEmployer = (float) ($bpjsEmployment['details']['jkm']['employer'] ?? 0);
        $jpEmployer = (float) ($bpjsEmployment['details']['jp']['employer'] ?? 0);
        $healthEmployer = (float) ($bpjsHealth['employer_total'] ?? 0);

        // Bruto = Salary + Allowances + Accrued Benefits (smoothed) + BPJS Employer (Taxable parts)
        $bruto = $taxableIncome + $healthEmployer + $jkkEmployer + $jkmEmployer + $jpEmployer;

        // 3. Find TER Rate
        $terRate = TaxRateTer::where('category', $category)
            ->where('min_gross', '<=', $bruto)
            ->where('max_gross', '>=', $bruto)
            ->where('is_active', true)
            ->first();

        $rate = (float) ($terRate?->rate ?? 0);
        $totalTax = $bruto * ($rate / 100);

        return [
            'total' => $totalTax,
            'rate' => $rate,
            'bruto' => $bruto,
            'category' => $category,
            'details' => [
                'ptkp_code' => $ptkpCode,
                'jkk' => $jkkEmployer,
                'jkm' => $jkmEmployer,
                'health' => $healthEmployer,
            ],
        ];
    }
}
