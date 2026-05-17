<?php

namespace Modules\Finance\Services;

use Modules\MasterData\Enums\RiskLevel;
use Modules\MasterData\Models\BpjsBasisType;
use Modules\MasterData\Models\BpjsHealthConfig;
use Modules\MasterData\Models\BpjsJhtConfig;
use Modules\MasterData\Models\BpjsJkkConfig;
use Modules\MasterData\Models\BpjsJkmConfig;
use Modules\MasterData\Models\BpjsJpConfig;
use Modules\MasterData\Models\MinimumWage;
use Modules\MasterData\Models\TaxPasal17Rate;
use Modules\MasterData\Models\TaxPtkpConfig;
use Modules\MasterData\Models\TaxTerRate;
use Modules\MasterData\Models\ThrBasisType;
use Modules\MasterData\Models\WorkPattern;
use Modules\MasterData\Models\WorkScheme;

class ManpowerCostingService
{
    protected static array $umkCache = [];

    protected static array $bpjsCache = [];

    public function calculateForTemplateItem(\Modules\CRM\Models\ManpowerTemplateItem $item): array
    {
        $allowances = $item->allowances ?? [];

        // If the item has a job position, we might want to merge or default allowances
        // but currently we assume $item->allowances is the source of truth for the template.

        $borneByCompany = [
            'jkn' => $item->is_employee_jkn_borne_by_company,
            'jkk' => $item->is_employee_jkk_borne_by_company,
            'jkm' => $item->is_employee_jkm_borne_by_company,
            'jht' => $item->is_employee_jht_borne_by_company,
            'jp' => $item->is_employee_jp_borne_by_company,
            'tax' => $item->is_tax_borne_by_company,
        ];

        return $this->calculate(
            basicSalary: (float) $item->basic_salary,
            allowances: $allowances,
            projectAreaId: $item->template?->project_area_id,
            year: $item->template?->year,
            workSchemeId: null, // Default
            workPatternId: $item->work_pattern_id,
            riskLevel: $item->risk_level ?? 'very_low',
            isLaborIntensive: (bool) $item->is_labor_intensive,
            employeeType: $item->employee_type ?? 'ppu',
            jknCategory: $item->jkn_category ?? 'PPU',
            thrBillingMethod: $item->thr_billing_method ?? 'monthly_accrual',
            compensationBillingMethod: $item->compensation_billing_method ?? 'monthly_accrual',
            thrBasisId: $item->thr_basis_id,
            compensationBasisId: $item->compensation_basis_id,
            bpjsBasisId: $item->bpjs_basis_id,
            billThrMonthly: (bool) $item->bill_thr_monthly,
            billCompensationMonthly: (bool) $item->bill_compensation_monthly,
            includeNonFixedInAccruals: (bool) $item->include_non_fixed_in_accruals,
            extraCosts: $item->extra_costs ?? [],
            adminFeePercentage: (float) ($item->template->admin_fee_percentage ?? 0),
            managementFeeFlat: (float) ($item->template->management_fee_flat ?? 0),
            ptkpCode: $item->ptkp_status ?? 'TK/0',
            isBpjsActive: (bool) $item->is_bpjs_active,
            borneByCompany: $borneByCompany,
            salaryIncreaseRate: (float) ($item->future_adjustment_rate ?? 0),
            baseYear: $item->template?->year,
            useTerMethod: (bool) $item->use_ter_method,
            contractTypeId: $item->contract_type_id
        );
    }

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
        array $borneByCompany = [], // ['jkn' => bool, 'jkk' => bool, etc]
        float $salaryIncreaseRate = 0.063, // 6.3% annual increase assumption
        ?int $baseYear = null,
        bool $useTerMethod = true,
        ?string $contractTypeId = null
    ): array {
        $baseYear = $baseYear ?? $year ?? (int) date('Y');
        $yearDelta = max(0, ($year ?? $baseYear) - $baseYear);

        // Apply annual increase assumption if year is ahead of base year
        if ($yearDelta > 0 && $salaryIncreaseRate > 0) {
            $multiplier = pow(1 + $salaryIncreaseRate, $yearDelta);
            $basicSalary *= $multiplier;

            // Also increase nominal allowances
            foreach ($allowances as &$allowance) {
                if (($allowance['type'] ?? 'nominal') === 'nominal') {
                    $allowance['value'] = ($allowance['value'] ?? $allowance['amount'] ?? 0) * $multiplier;
                }
            }
        }

        $cacheKey = "{$projectAreaId}-{$year}";

        if (isset(self::$umkCache[$cacheKey])) {
            $umk = self::$umkCache[$cacheKey];
        } else {
            $umk = 0;
            if (filled($projectAreaId) && filled($year)) {
                $umk = MinimumWage::where('project_area_id', $projectAreaId)
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
            $frequency = $allowance['frequency'] ?? 'monthly';

            $amount = $val;
            if ($type === 'percentage') {
                $baseType = $allowance['base_type'] ?? 'basic_salary';
                $baseMultiplier = $baseType === 'umk' ? $umk : $basicSalary;
                $amount = $baseMultiplier * ($val / 100);
            }

            if ($frequency === 'daily') {
                $amount *= $workingDays;
            }

            if ($isFixed) {
                $fixedAllowances += $amount;
            } else {
                $nonFixedAllowances += $amount;
            }
        }

        $upah = $basicSalary + $fixedAllowances;
        $totalMonthlySalary = $upah + $nonFixedAllowances;

        // Determine Basis for BPJS, THR, Compensation
        $bpjsBasisAmount = $this->calculateBasisAmount($basicSalary, $umk, $allowances, $bpjsBasisId, 'bpjs', false, $workingDays);
        $thrBasisAmount = $this->calculateBasisAmount($basicSalary, $umk, $allowances, $thrBasisId, 'thr', $includeNonFixedInAccruals, $workingDays);
        $compensationBasisAmount = $this->calculateBasisAmount($basicSalary, $umk, $allowances, $compensationBasisId, 'compensation', $includeNonFixedInAccruals, $workingDays);

        $contractTypeCode = null;
        if ($contractTypeId) {
            $contractTypeCode = \Modules\MasterData\Models\ContractType::where('id', $contractTypeId)->value('code');
        }
        $contractTypeCode = $contractTypeCode ?: 'PKWT';

        if ($contractTypeCode === 'MITRA') {
            $isBpjsActive = false;
        }

        // BPJS Calculation
        if ($isBpjsActive) {
            $bpjsHealth = $this->calculateBpjsHealth($bpjsBasisAmount, $umk, $employeeType, $jknCategory, $borneByCompany['jkn'] ?? false);
            $bpjsEmployment = $this->calculateBpjsEmployment($bpjsBasisAmount, $riskLevel, $isLaborIntensive, $employeeType, $borneByCompany);
        } else {
            $bpjsHealth = ['employer' => 0, 'employee' => 0, 'employer_total' => 0, 'base' => 0];
            $bpjsEmployment = ['employer_total' => 0, 'employee_total' => 0, 'details' => []];
        }

        // Accrual Basis (THR/Comp may include non-fixed depending on position logic)
        $thr = 0.0;
        $compensation = 0.0;

        if ($contractTypeCode !== 'MITRA') {
            $thr = $thrBillingMethod === 'monthly_accrual' ? ($thrBasisAmount / 12) : 0;
            if ($contractTypeCode === 'PKWT') {
                $compensation = $compensationBillingMethod === 'monthly_accrual' ? ($compensationBasisAmount / 12) : 0;
            }
        }

        // Extra monthly costs by category
        $equipmentTotal = 0.0;
        $trainingTotal = 0.0;
        $bufferTotal = 0.0;
        $otherCostTotal = 0.0;

        foreach ($extraCosts as $item) {
            $cat = $item['category'] ?? 'other';
            $amount = (float) ($item['amount'] ?? 0);

            if ($cat === 'equipment') {
                $equipmentTotal += $amount;
            } elseif ($cat === 'training') {
                $trainingTotal += $amount;
            } elseif ($cat === 'buffer') {
                $bufferTotal += $amount;
            } else {
                $otherCostTotal += $amount;
            }
        }

        $extraCostsTotal = $equipmentTotal + $trainingTotal + $bufferTotal + $otherCostTotal;

        // PPH21 Calculation (Based on monthly salary, excluding accruals)
        if ($useTerMethod) {
            $pph21 = $this->calculatePph21($totalMonthlySalary, $bpjsHealth, $bpjsEmployment, $ptkpCode);
        } else {
            $pph21 = $this->calculateProgressivePph21($totalMonthlySalary, $bpjsHealth, $bpjsEmployment, $ptkpCode);
        }

        // Management Fee Calculation
        $totalDirectCost = $upah + $nonFixedAllowances + $bpjsHealth['employer_total'] + $bpjsEmployment['employer_total'] + $thr + $compensation + $extraCostsTotal;

        if ($borneByCompany['tax'] ?? false) {
            $totalDirectCost += $pph21['total'];
        }

        $adminFee = $totalDirectCost * ($adminFeePercentage / 100);

        $totalCostToCompany = $totalDirectCost + $adminFee + $managementFeeFlat;

        $breakdown = [
            'Gaji Pokok' => $basicSalary,
            'Tunjangan Jabatan' => 0.0,
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
            'PPh 21 ('.($useTerMethod ? 'TER' : 'Pasal 17').')' => $pph21['total'],
            'Seragam & Perlengkapan' => $equipmentTotal,
            'Training & Sertifikasi' => $trainingTotal,
            'Biaya Buffer/Inval' => $bufferTotal,
            'Biaya Lainnya' => $otherCostTotal,
            'SUBTOTAL DIRECT COST' => $totalDirectCost,
            'Management Fee' => $adminFee + $managementFeeFlat,
            'TOTAL BILLING' => $totalCostToCompany,
        ];

        // Map allowances to specific breakdown keys
        foreach ($allowances as $allowance) {
            $name = $allowance['name'] ?? '';
            $val = (float) ($allowance['value'] ?? $allowance['amount'] ?? 0);
            $type = $allowance['type'] ?? 'nominal';
            $freq = $allowance['frequency'] ?? 'monthly';
            $amount = $val;
            if ($type === 'percentage') {
                $baseType = $allowance['base_type'] ?? 'basic_salary';
                $baseMultiplier = $baseType === 'umk' ? $umk : $basicSalary;
                $amount = $baseMultiplier * ($val / 100);
            }

            if ($freq === 'daily') {
                $amount *= $workingDays;
            }

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
            'salary_increase_rate' => $salaryIncreaseRate,
            'year_delta' => $yearDelta,
            'total_direct_cost' => $totalDirectCost,
            'total_cost_to_company' => $totalCostToCompany,
            'total_non_fixed_allowances' => $nonFixedAllowances,
            'bpjs_total' => $bpjsHealth['employer_total'] + $bpjsEmployment['employer_total'],
            'breakdown' => $breakdown,
        ];
    }

    protected function calculateBasisAmount(float $basicSalary, float $umk, array $allowances, ?string $basisId, string $type, bool $legacyIncludeNonFixed = false, int $workingDays = 21): float
    {
        $basis = null;
        if ($basisId) {
            $basis = ($type === 'bpjs')
                ? BpjsBasisType::find($basisId)
                : ThrBasisType::find($basisId);
        }

        if (! $basis) {
            $fixedTotal = 0.0;
            foreach ($allowances as $allowance) {
                if ($allowance['is_fixed'] ?? true) {
                    $val = (float) ($allowance['value'] ?? $allowance['amount'] ?? 0);
                    $freq = $allowance['frequency'] ?? 'monthly';
                    $amt = $val;
                    if (($allowance['type'] ?? 'nominal') === 'percentage') {
                        $baseType = $allowance['base_type'] ?? 'basic_salary';
                        $baseMultiplier = $baseType === 'umk' ? $umk : $basicSalary;
                        $amt = $baseMultiplier * ($val / 100);
                    }
                    if ($freq === 'daily') {
                        $amt *= $workingDays;
                    }
                    $fixedTotal += $amt;
                }
            }

            $amount = $basicSalary + $fixedTotal;
            if ($legacyIncludeNonFixed) {
                foreach ($allowances as $allowance) {
                    if (! ($allowance['is_fixed'] ?? true)) {
                        $val = (float) ($allowance['value'] ?? $allowance['amount'] ?? 0);
                        $freq = $allowance['frequency'] ?? 'monthly';
                        $amt = $val;
                        if (($allowance['type'] ?? 'nominal') === 'percentage') {
                            $baseType = $allowance['base_type'] ?? 'basic_salary';
                            $baseMultiplier = $baseType === 'umk' ? $umk : $basicSalary;
                            $amt = $baseMultiplier * ($val / 100);
                        }
                        if ($freq === 'daily') {
                            $amt *= $workingDays;
                        }
                        $amount += $amt;
                    }
                }
            }

            return $amount;
        }

        $formula = $basis->formula_code;
        $total = $basicSalary;

        foreach ($allowances as $allowance) {
            $val = (float) ($allowance['value'] ?? $allowance['amount'] ?? 0);
            $freq = $allowance['frequency'] ?? 'monthly';
            $amt = ($allowance['type'] ?? 'nominal') === 'percentage' ? ($basicSalary * ($val / 100)) : $val;
            if ($freq === 'daily') {
                $amt *= $workingDays;
            }
            $isFixed = $allowance['is_fixed'] ?? true;
            $isBpjsBase = $allowance['is_bpjs_base'] ?? false;

            if ($formula === 'gaji_plus_tetap' || $formula === 'gaji_plus_tunjangan_tetap') {
                if ($isFixed) {
                    $total += $amt;
                }
            } elseif ($formula === 'gaji_plus_tetap_plus_sebagian') {
                if ($isFixed || $isBpjsBase) {
                    $total += $amt;
                }
            }
        }

        return $total;
    }

    protected function calculateBpjsHealth(float $upah, float $umk, string $employeeType, string $jknCategory = 'PPU', bool $isBorneByCompany = false): array
    {
        /** @var BpjsHealthConfig|null $config */
        $config = BpjsHealthConfig::where('employee_type', $employeeType)
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
        /** @var BpjsJkkConfig|null $jkkConfig */
        $jkkConfig = BpjsJkkConfig::where('employee_type', $employeeType)
            ->when($employeeType === 'ppu', function ($q) use ($riskLevel) {
                /** @var \Illuminate\Database\Eloquent\Builder $q */
                return $q->where('risk_level', $riskLevel);
            })
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
        /** @var BpjsJkmConfig|null $jkmConfig */
        $jkmConfig = BpjsJkmConfig::where('employee_type', $employeeType)->where('is_active', true)->first();
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
        /** @var BpjsJhtConfig|null $jhtConfig */
        $jhtConfig = BpjsJhtConfig::where('employee_type', $employeeType)->where('is_active', true)->first();
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
        /** @var BpjsJpConfig|null $jpConfig */
        $jpConfig = BpjsJpConfig::where('employee_type', $employeeType)->where('is_active', true)->first();
        if ($jpConfig) {
            $base = min($upah, (float) ($jpConfig->cap_nominal ?? 11086300)); // Updated to 2025 Cap
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
        $ptkp = TaxPtkpConfig::where('code', $ptkpCode)->first();
        $category = $ptkp?->tax_category ?? 'A';

        $jkkEmployer = (float) ($bpjsEmployment['details']['jkk']['employer'] ?? 0);
        $jkmEmployer = (float) ($bpjsEmployment['details']['jkm']['employer'] ?? 0);
        $healthEmployer = (float) ($bpjsHealth['employer'] ?? 0);

        // Add portions borne by company (employee share paid by employer)
        $jhtEePaidByEr = (float) (($bpjsEmployment['details']['jht']['line_total'] ?? 0) - ($bpjsEmployment['details']['jht']['employer'] ?? 0));
        $jpEePaidByEr = (float) (($bpjsEmployment['details']['jp']['line_total'] ?? 0) - ($bpjsEmployment['details']['jp']['employer'] ?? 0));
        $healthEePaidByEr = (float) (($bpjsHealth['employer_total'] ?? 0) - ($bpjsHealth['employer'] ?? 0));

        $bruto = $taxableIncome + $healthEmployer + $jkkEmployer + $jkmEmployer + $jhtEePaidByEr + $jpEePaidByEr + $healthEePaidByEr;

        $ter = TaxTerRate::where('category', $category)
            ->where('min_gross', '<=', $bruto)->where('max_gross', '>=', $bruto)->where('is_active', true)->first();

        $rate = (float) ($ter?->rate ?? 0);
        $totalTax = $bruto * ($rate / 100);

        return ['total' => $totalTax, 'rate' => $rate, 'bruto' => $bruto, 'category' => $category];
    }

    protected function calculateProgressivePph21(float $monthlyIncome, array $bpjsHealth, array $bpjsEmployment, string $ptkpCode = 'TK/0'): array
    {
        $ptkp = TaxPtkpConfig::where('code', $ptkpCode)->first();
        $ptkpValue = (float) ($ptkp?->annual_amount ?? 54000000);

        $jkkEmployer = (float) ($bpjsEmployment['details']['jkk']['employer'] ?? 0);
        $jkmEmployer = (float) ($bpjsEmployment['details']['jkm']['employer'] ?? 0);
        $healthEmployer = (float) ($bpjsHealth['employer'] ?? 0);

        $jhtEmployee = (float) ($bpjsEmployment['details']['jht']['employee'] ?? 0);
        $jpEmployee = (float) ($bpjsEmployment['details']['jp']['employee'] ?? 0);

        // Add portions borne by company (employee share paid by employer)
        $jhtEePaidByEr = (float) (($bpjsEmployment['details']['jht']['line_total'] ?? 0) - ($bpjsEmployment['details']['jht']['employer'] ?? 0));
        $jpEePaidByEr = (float) (($bpjsEmployment['details']['jp']['line_total'] ?? 0) - ($bpjsEmployment['details']['jp']['employer'] ?? 0));
        $healthEePaidByEr = (float) (($bpjsHealth['total_total'] ?? 0) - ($bpjsHealth['employer'] ?? 0));

        $brutoMonthly = $monthlyIncome + $healthEmployer + $jkkEmployer + $jkmEmployer + $jhtEePaidByEr + $jpEePaidByEr + $healthEePaidByEr;
        $biayaJabatan = min(500000, $brutoMonthly * 0.05);
        $netMonthly = $brutoMonthly - $biayaJabatan - $jhtEmployee - $jpEmployee;

        $netYearly = $netMonthly * 12;
        $pkp = max(0, floor(($netYearly - $ptkpValue) / 1000) * 1000);

        $totalTaxYearly = 0;
        $remainingPkp = $pkp;
        $rates = TaxPasal17Rate::where('is_active', true)->orderBy('min_pkp', 'asc')->get();

        foreach ($rates as $rate) {
            $layerWidth = ($rate->max_pkp ? ($rate->max_pkp - $rate->min_pkp) : INF);
            $taxableInLayer = min($remainingPkp, $layerWidth);
            $totalTaxYearly += $taxableInLayer * ($rate->rate / 100);
            $remainingPkp -= $taxableInLayer;
            if ($remainingPkp <= 0) {
                break;
            }
        }

        return ['total' => $totalTaxYearly / 12, 'bruto' => $brutoMonthly, 'net_yearly' => $netYearly, 'pkp' => $pkp];
    }
}
