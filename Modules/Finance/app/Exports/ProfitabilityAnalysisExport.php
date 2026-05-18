<?php

namespace Modules\Finance\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Finance\Services\ManpowerCostingService;
use Modules\MasterData\Enums\ApprovalSignatureType;
use Modules\MasterData\Models\ApprovalRule;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Services\SignatureService;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\Permission\Models\Role;

class ProfitabilityAnalysisExport implements FromView, ShouldAutoSize, WithColumnFormatting, WithColumnWidths, WithStyles, WithTitle
{
    public function __construct(
        protected ProfitabilityAnalysis $record
    ) {}

    public function view(): View
    {
        return view('finance::exports.profitability-analysis-excel', [
            'record' => $this->record,
            'data' => $this->getComplicatedSummary(),
            'isExport' => true,
        ]);
    }

    protected function getComplicatedSummary(): array
    {
        $service = app(ManpowerCostingService::class);
        $pa = $this->record;
        $lead = $pa->lead;
        $gi = $pa->generalInformation;
        $paymentTerm = $pa->paymentTerm;
        $topDays = (float) ($paymentTerm?->days ?? 30);

        $sections = [
            'header' => [
                'number' => $pa->number ?? '-',
                'ams' => $pa->lead?->salesPlan?->ams?->name ?? $pa->ams?->name ?? $pa->lead?->ams?->name ?? '-',
                'customer' => $pa->customer?->name ?? '-',
                'project_name' => $pa->lead?->title ?? '-',
                'project_code' => $pa->project?->number ?? $pa->lead?->salesPlan?->project_code ?? '-',
                'revision' => sprintf('%02d', $pa->revision_number ?? 0),
                'start_date' => $pa->start_date?->format('d-M-y') ?? '-',
                'end_date' => $pa->end_date?->format('d-M-y') ?? '-',
                'top' => $pa->payment_term ?? $paymentTerm?->name ?? '-',
                'remarks' => $pa->remarks ?? $pa->lead?->description ?? '-',
            ],
            'revenue' => [
                'items' => [
                    ['name' => 'Price', 'qty' => null, 'amount' => (float) $pa->revenue_per_month - (float) $pa->management_fee],
                    ['name' => 'Management Fee or Targeted Gross Profit Margin', 'qty' => number_format((float) $pa->management_fee_rate, 2).'%', 'amount' => (float) $pa->management_fee],
                ],
                'total' => (float) $pa->revenue_per_month,
            ],
            'direct_cost' => [
                'manpower' => [
                    'base' => ['name' => 'Manpower', 'qty' => 0, 'amount' => 0],
                    'team_leader' => ['name' => 'Team Leader', 'qty' => 0, 'amount' => 0],
                    'variable' => ['name' => 'Variable Manpower (Tunjangan Tidak Tetap)', 'qty' => 0, 'amount' => 0],
                    'benefit' => ['name' => 'BPJS, THR, IPK, Perlengkapan Kerja Ditanggung Perusahaan', 'qty' => 0, 'amount' => 0],
                ],
                'operational' => [
                    'Tools & Equipment' => ['qty' => 0, 'amount' => 0],
                    'Material' => ['qty' => 0, 'amount' => 0],
                    'IT System' => ['qty' => 0, 'amount' => 0],
                    'Warranty' => ['qty' => 0, 'amount' => 0],
                    'Infrastructure Support' => ['qty' => 0, 'amount' => 0],
                    'Others' => ['qty' => 0, 'amount' => 0],
                ],
                'total' => (float) $pa->direct_cost,
            ],
            'gp' => [
                'amount' => (float) ($pa->revenue_per_month - $pa->direct_cost),
                'margin' => (float) $pa->margin_percentage,
            ],
            'indirect_cost' => [
                'categories' => [
                    'Management Expense' => ['amount' => 0],
                    'Entertainment' => ['amount' => 0],
                    'Business Partner' => ['amount' => 0],
                    'Concession' => ['amount' => 0],
                ],
                'total' => $pa->getTotalIndirectCost(),
            ],
            'financial' => [
                'ebitda' => (float) $pa->ebitda,
                'depreciation' => (float) $pa->depreciation,
                'ebit' => (float) $pa->ebit,
                'interest' => (float) ($pa->ebit - $pa->ebt),
                'ebt' => (float) $pa->ebt,
                'tax' => (float) ($pa->ebt - $pa->net_profit),
                'net_profit' => (float) $pa->net_profit,
                'npm' => (float) $pa->net_profit_margin,
            ],
            'signatures' => [
                'reviewer' => $this->getSignaturesByType(ApprovalSignatureType::Reviewer),
                'margin_approval' => $this->getSignaturesByType(ApprovalSignatureType::MarginApproval),
                'approver' => $this->getSignaturesByType(ApprovalSignatureType::Approver),
                'acknowledger' => $this->getSignaturesByType(ApprovalSignatureType::Acknowledger),
            ],
        ];

        // 1. Direct Costs (Manpower & Operational)
        $directItems = $pa->getDirectItems();
        foreach ($directItems as $item) {
            $catName = $item->category?->name ?? 'Others';
            // User requested to hide qty if it's a manual process because relation data is unavailable
            $qty = $pa->is_manual_cost ? 0 : (float) ($item->quantity ?? 0);
            $amount = (float) ($item->total_monthly_cost ?? 0);

            // Check if it's manpower
            $isManpower = (($item->category?->code ?? null) === 'manpower') ||
                         (($item->costable_type ?? null) === JobPosition::class) ||
                         (isset($item->is_manpower) && $item->is_manpower);

            if ($isManpower) {
                // Fallback for manual cost entries that are flat amounts
                $sections['direct_cost']['manpower']['base']['qty'] += $qty;
                $sections['direct_cost']['manpower']['base']['amount'] += $amount;
            } else {
                // Operational Cost Grouping
                $matchedKey = 'Others';
                foreach (array_keys($sections['direct_cost']['operational']) as $key) {
                    if (str_contains(strtolower($catName), str_replace(' Support', '', strtolower(str_replace(' & Equipment', '', $key))))) {
                        $matchedKey = $key;
                        break;
                    }
                }
                $sections['direct_cost']['operational'][$matchedKey]['qty'] += $qty;
                $sections['direct_cost']['operational'][$matchedKey]['amount'] += $amount;
            }
        }

        // 2. Indirect Costs Mapping
        $indirectItems = $pa->getIndirectItems();
        foreach ($indirectItems as $item) {
            $catName = $item->category?->name ?? 'Others';

            if (($item->calculation_type ?? 'fixed') === 'percentage') {
                $val = (float) ($item->unit_cost_price ?? 0);
                $basis = $item->percentage_basis ?? 'revenue';
                $basisValue = $basis === 'revenue' ? (float) $pa->revenue_per_month : (float) $pa->direct_cost;
                $finalVal = $basisValue * ($val / 100);
            } else {
                $finalVal = (float) ($item->total_monthly_cost ?? $item->unit_cost_price ?? 0);
            }
            $amount = $finalVal;

            // Specific overrides to match spreadsheet categories
            if (str_contains($catName, 'Management Expense')) {
                $catName = 'Management Expense';
            } elseif (str_contains($catName, 'Entertainment')) {
                $catName = 'Entertainment';
            } elseif (str_contains($catName, 'Business Partner')) {
                $catName = 'Business Partner';
            } elseif (str_contains($catName, 'Concession')) {
                $catName = 'Concession';
            }

            if (! isset($sections['indirect_cost']['categories'][$catName])) {
                $sections['indirect_cost']['categories'][$catName] = ['amount' => 0];
            }
            $sections['indirect_cost']['categories'][$catName]['amount'] += $amount;
        }

        return $sections;
    }

    protected function getSignaturesByType(ApprovalSignatureType $type): array
    {
        $signatures = $this->record->signatures()
            ->where('signature_type', $type->value)
            ->get();

        $items = [];
        $service = app(SignatureService::class);
        $rules = $service->getRequiredApprovers($this->record);
        $specificRules = $rules->filter(function ($r) use ($type) {
            /** @var \Modules\MasterData\Models\ApprovalRule $r */
            return $r->signature_type === $type;
        });

        // Map existing signatures
        foreach ($signatures as $signature) {
            $qrCode = null;
            try {
                $qrData = $service->createSignatureData(
                    $signature->user,
                    $this->record,
                    $signature->signature_type,
                    $signature->user_id ? null : $signature->signer_name
                );
                $qrCode = $service->generateQRCode($qrData);
            } catch (\Exception $e) {
            }

            $items[] = [
                'name' => $signature->signer_name ?? $signature->user?->name ?? '-',
                'title' => $signature->signer_title ?? $signature->role ?? '-',
                'date' => $signature->signed_at?->format('d M Y') ?? '-',
                'is_signed' => true,
                'qr_code' => $qrCode,
            ];
        }

        // Add pending if no signature match for required rules
        foreach ($specificRules as $rule) {
            $hasSig = $signatures->contains(function ($s) use ($service, $rule) {
                /** @var \Modules\MasterData\Models\ApprovalSignature $s */
                return $service->isEligibleApprover($rule, $s->user);
            });
            if (! $hasSig) {
                $items[] = $this->getExpectedApproverForRule($rule, $type);
            }
        }

        return $items;
    }

    protected function getExpectedApproverForRule(ApprovalRule $rule, ApprovalSignatureType $type): array
    {
        $service = app(SignatureService::class);
        $title = $type->getLabel();
        $name = '-';

        // 1. Resolve Title (Role Names or Position)
        if ($rule->approver_type === 'Role') {
            $roleIds = $rule->approver_role ?? [];
            if (! empty($roleIds)) {
                $roleNames = Role::whereIn('id', $roleIds)
                    ->orWhereIn('name', $roleIds)
                    ->pluck('name')
                    ->toArray();

                $title = implode(', ', $roleNames);
            }
        } elseif ($rule->approver_type === 'Position') {
            $title = implode(', ', $rule->approver_position ?? []);
        }

        // 2. Resolve Names (Eligible Users)
        $eligibleUsers = $service->getEligibleUsers($rule);
        if ($eligibleUsers->isNotEmpty()) {
            $name = $eligibleUsers->pluck('name')->implode(', ');

            // If it's a 'User' type rule, the title should be their position
            if ($rule->approver_type === 'User') {
                $title = $eligibleUsers->first()?->position ?? $title;
            }
        }

        return [
            'name' => $name,
            'title' => $title,
            'date' => '-',
            'is_signed' => false,
            'qr_code' => null,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Row 1: Title (matches Blade row 1)
            1 => ['font' => ['bold' => true, 'size' => 16]],
            // Row 2: Subtitle/Doc Number (matches Blade row 2)
            2 => ['font' => ['bold' => true]],
            // Column A, B, C: Default alignment and font
            'A' => ['alignment' => ['vertical' => 'center']],
            'B' => ['alignment' => ['horizontal' => 'right', 'vertical' => 'center']],
            'C' => ['alignment' => ['horizontal' => 'right', 'vertical' => 'center']],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 50,
            'B' => 20,
            'C' => 30,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function title(): string
    {
        return 'Profitability Analysis - '.($this->record->number ?? $this->record->id);
    }
}
