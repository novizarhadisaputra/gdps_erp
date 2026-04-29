<?php

namespace Modules\Finance\Services;

use Modules\MasterData\Enums\RiskLevel;
use Modules\MasterData\Models\BpjsBasisType;
use Modules\MasterData\Models\HealthConfig;
use Modules\MasterData\Models\JhtConfig;
use Modules\MasterData\Models\JkkConfig;
use Modules\MasterData\Models\JkmConfig;
use Modules\MasterData\Models\JpConfig;
use Modules\MasterData\Models\PtkpConfig;
use Modules\MasterData\Models\RegencyMinimumWage;
use Modules\MasterData\Models\TaxRateTer;
use Modules\MasterData\Models\ThrBasisType;
use Modules\MasterData\Models\WorkPattern;
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
        ?string $workPatternId = null,
        string|RiskLevel $riskLevel = 'very_low',
        bool $isLaborIntensive = false,
        string $employeeType = 'ppu', // ppu or pbpu
        string $jknCategory = 'PPU',
        string $thrBillingMethod = 'monthly_accrual',
        string $compensationBillingMethod = 'monthly_accrual',
        ?string $thrBasisId = null,
        ?string $compensationBasisId = null,
        ?string $bpjsBasisId = null,
        bool $billThrMonthly = true,
        bool $billCompensationMonthly = true,
        bool $includeNonFixedInAccruals = false,
        array $extraCosts = [], // Monthly flat costs (Equipments/Trainings)
        float $adminFeePercentage = 0.0,
        float $managementFeeFlat = 0.0,
        string $ptkpCode = 'TK/0',
        bool $isBpjsActive = true,
        array $borneByCompany = [] // ['jkn' => bool, 'jkk' => bool, etc]
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
        $workPattern = $workPatternId ? WorkPattern::find($workPatternId) : null;

        $workingDays = $workPattern?->days_per_week ? ($workPattern->days_per_week * 4) : ($workScheme?->working_days ?? 21);

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

        // Determine Basis for BPJS, THR, Compensation
        $bpjsBasisAmount = $this->calculateBasisAmount($basicSalary, $allowances, $bpjsBasisId, 'bpjs');
        $thrBasisAmount = $this->calculateBasisAmount($basicSalary, $allowances, $thrBasisId, 'thr', $includeNonFixedInAccruals);
        $compensationBasisAmount = $this->calculateBasisAmount($basicSalary, $allowances, $compensationBasisId, 'compensation', $includeNonFixedInAccruals);

        // BPJS Calculation
        if ($isBpjsActive) {
            $bpjsHealth = $this->calculateBpjsHealth($bpjsBasisAmount, $umk, $employeeType, $jknCategory, $borneByCompany['jkn'] ?? false);
            $bpjsEmployment = $this->calculateBpjsEmployment($bpjsBasisAmount, $riskLevel, $isLaborIntensive, $employeeType, $borneByCompany);
        } else {
            $bpjsHealth = ['employer' => 0, 'employee' => 0, 'employer_total' => 0, 'base' => 0];
            $bpjsEmployment = ['employer_total' => 0, 'employee_total' => 0, 'details' => []];
        }

        // Accrual Basis (THR/Comp may include non-fixed depending on position logic)
        $thr = $thrBillingMethod === 'monthly_accrual' ? ($thrBasisAmount / 12) : 0;
        $compensation = $compensationBillingMethod === 'monthly_accrual' ? ($compensationBasisAmount / 12) : 0;

        // Extra monthly costs which are flat (Equipment/Training)
        $extraCostsTotal = array_reduce($extraCosts, fn ($carry, $item) => $carry + (float) ($item['amount'] ?? 0), 0);

        // PPH21 Calculation
        $pph21 = $this->calculatePph21($totalMonthlySalary + $thr + $compensation, $bpjsHealth, $bpjsEmployment, $ptkpCode);

        // Management Fee Calculation
        // Management fee base usually includes all costs plus PPh 21 (if borne by company)
        $totalDirectCost = $upah + $nonFixedAllowances + $bpjsHealth['employer_total'] + $bpjsEmployment['employer_total'] + $thr + $compensation + $extraCostsTotal;

        if ($borneByCompany['tax'] ?? false) {
            $totalDirectCost += $pph21['total'];
        }

        $adminFee = $totalDirectCost * ($adminFeePercentage / 100);

        $totalCostToCompany = $totalDirectCost + $adminFee + $managementFeeFlat;

        $breakdown = [
            'Gaji Pokok' => $basicSalary,
            'Tunjangan Jabatan' => 0.0, // Will be filled from allowances logic
            'Tunjangan Komunikasi' => 0.0,
            'Tunjangan Sertifikasi' => 0.0,
            'SUBTOTAL UPAH' => $upah,
            'BPJS Kesehatan (ER)' => $bpjsHealth['employer_total'],
            'BPJS JKK (ER)' => $bpjsEmployment['details']['jkk']['employer'] ?? 0,
            'BPJS JKM (ER)' => $bpjsEmployment['details']['jkm']['employer'] ?? 0,
            'BPJS JHT (ER)' => $bpjsEmployment['details']['jht']['employer'] ?? 0,
            'BPJS JP (ER)' => $bpjsEmployment['details']['jp']['employer'] ?? 0,
            'SUBTOTAL BPJS' => $bpjsHealth['employer_total'] + $bpjsEmployment['employer_total'],
            'Tunjangan Transport' => 0.0,
            'Tunjangan Makan' => 0.0,
            'Lembur (Overtime)' => 0.0,
            'SUBTOTAL TUNJANGAN NON-TETAP' => $nonFixedAllowances,
            'THR (Accrual)' => $thr,
            'Kompensasi (Accrual)' => $compensation,
            'PPh 21 (TER)' => $pph21['total'],
            'Seragam & Perlengkapan' => $extraCostsTotal,
            'Training & Sertifikasi' => 0.0, // Split if needed
            'SUBTOTAL DIRECT COST' => $totalDirectCost,
            'Management Fee' => $adminFee + $managementFeeFlat,
            'TOTAL BILLING' => $totalCostToCompany,
        ];

        // Map allowances to specific breakdown keys if they match by name (case-insensitive)
        foreach ($allowances as $allowance) {
            $name = $allowance['name'] ?? '';
            $val = (float) ($allowance['value'] ?? $allowance['amount'] ?? 0);
            $type = $allowance['type'] ?? 'nominal';
            $amount = $type === 'percentage' ? ($basicSalary * ($val / 100)) : $val;

            foreach ($breakdown as $key => $bVal) {
                if (strtolower($key) === strtolower($name)) {
                    $breakdown[$key] = $amount;
                }
            }
        }

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
                'basis' => $thrBasisAmount,
            ],
            'extra_costs' => $extraCosts,
            'extra_costs_total' => $extraCostsTotal,
            'admin_fee' => $adminFee,
            'management_fee' => $managementFeeFlat,
            'total_direct_cost' => $totalDirectCost,
            'total_cost_to_company' => $totalCostToCompany,
            'total_allowances' => $nonFixedAllowances,
            'bpjs_total' => $bpjsHealth['employer_total'] + $bpjsEmployment['employer_total'],
            'breakdown' => $breakdown,
        ];
    }

    protected function calculateBasisAmount(float $basicSalary, array $allowances, ?string $basisId, string $type, bool $legacyIncludeNonFixed = false): float
    {
        $basis = null;
        if ($basisId) {
            $basis = ($type === 'bpjs')
                ? BpjsBasisType::find($basisId)
                : ThrBasisType::find($basisId);
        }

        if (! $basis) {
            // Default logic if no basis selected
            $fixedTotal = 0.0;
            foreach ($allowances as $allowance) {
                if ($allowance['is_fixed'] ?? true) {
                    $val = (float) ($allowance['value'] ?? $allowance['amount'] ?? 0);
                    $fixedTotal += $allowance['type'] === 'percentage' ? ($basicSalary * ($val / 100)) : $val;
                }
            }

            $amount = $basicSalary + $fixedTotal;
            if ($legacyIncludeNonFixed) {
                foreach ($allowances as $allowance) {
                    if (! ($allowance['is_fixed'] ?? true)) {
                        $val = (float) ($allowance['value'] ?? $allowance['amount'] ?? 0);
                        $amount += $allowance['type'] === 'percentage' ? ($basicSalary * ($val / 100)) : $val;
                    }
                }
            }

            return $amount;
        }

        $formula = $basis->formula_code;
        $total = $basicSalary;

        foreach ($allowances as $allowance) {
            $val = (float) ($allowance['value'] ?? $allowance['amount'] ?? 0);
            $amt = $allowance['type'] === 'percentage' ? ($basicSalary * ($val / 100)) : $val;
            $isFixed = $allowance['is_fixed'] ?? true;
            $isBpjsBase = $allowance['is_bpjs_base'] ?? false;

            if ($formula === 'gaji_plus_tetap') {
                if ($isFixed) {
                    $total += $amt;
                }
            } elseif ($formula === 'gaji_plus_tetap_plus_sebagian') {
                if ($isFixed || $isBpjsBase) {
                    $total += $amt;
                }
            }
            // gaji_pokok = do nothing, total remains $basicSalary
        }

        return $total;
    }

    protected function calculateBpjsHealth(float $upah, float $umk, string $employeeType, string $jknCategory = 'PPU', bool $isBorneByCompany = false): array
    {
        /** @var HealthConfig|null $config */
        $config = HealthConfig::where('employee_type', $employeeType)
            ->where('is_active', true)
            ->first();

        if (! $config) {
            return ['employer' => 0, 'employee' => 0, 'employer_total' => 0, 'base' => 0];
        }

        $base = $upah;
        if ($employeeType === 'ppu' || $jknCategory === 'PPU') {
            $cap = (float) ($config->cap_nominal ?? 12000000); // BPJS Health cap 2024 is 12M
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
            'employer_total' => $isBorneByCompany ? ($employer + $employee) : $employer,
        ];
    }

    protected function calculateBpjsEmployment(float $upah, string $riskLevel, bool $isLaborIntensive, string $employeeType, array $borneByCompany = []): array
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

            $lineTotal = ($borneByCompany['jkk'] ?? false) ? ($employer + $employee) : $employer;
            $details['jkk'] = ['employer' => $employer, 'employee' => $employee, 'base' => $base, 'line_total' => $lineTotal];
            $employerTotal += $lineTotal;
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

            $lineTotal = ($borneByCompany['jkm'] ?? false) ? ($employer + $employee) : $employer;
            $details['jkm'] = ['employer' => $employer, 'employee' => $employee, 'base' => $base, 'line_total' => $lineTotal];
            $employerTotal += $lineTotal;
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

            $lineTotal = ($borneByCompany['jht'] ?? false) ? ($employer + $employee) : $employer;
            $details['jht'] = ['employer' => $employer, 'employee' => $employee, 'base' => $base, 'line_total' => $lineTotal];
            $employerTotal += $lineTotal;
            $employeeTotal += $employee;
        }

        // --- 4. JP --- //
        /** @var JpConfig|null $jpConfig */
        $jpConfig = JpConfig::where('employee_type', $employeeType)->where('is_active', true)->first();
        if ($jpConfig) {
            $base = min($upah, (float) ($jpConfig->cap_nominal ?? 10042300)); // JP Cap 2024 is 10,042,300
            $employer = $base * (float) $jpConfig->employer_rate;
            $employee = $base * (float) $jpConfig->employee_rate;

            $lineTotal = ($borneByCompany['jp'] ?? false) ? ($employer + $employee) : $employer;
            $details['jp'] = ['employer' => $employer, 'employee' => $employee, 'base' => $base, 'line_total' => $lineTotal];
            $employerTotal += $lineTotal;
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
        $healthEmployer = (float) ($bpjsHealth['employer'] ?? 0);

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
