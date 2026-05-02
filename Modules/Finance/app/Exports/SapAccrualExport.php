<?php

namespace Modules\Finance\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Finance\Services\AccrualMappingService;

class SapAccrualExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct(
        protected $records,
        protected AccrualMappingService $mappingService
    ) {}

    public function view(): View
    {
        $exportData = [];
        $now = now();

        foreach ($this->records as $record) {
            $projectArea = $record->projectArea;
            $customer = $record->customer;
            $revenueSegmentId = $record->project?->revenue_segment_id;
            $periodDate = \Carbon\Carbon::parse($record->accrual_period);

            foreach ($record->items as $item) {
                // Resolve Accrual Account (Debit)
                $accruedAccount = $this->mappingService->resolveAccount(
                    'accrual',
                    $projectArea,
                    $customer,
                    $item->revenue_type_id,
                    $revenueSegmentId
                ) ?? '12101010'; // Default placeholder

                // Resolve Revenue Account (Credit)
                $revenueAccount = $this->mappingService->resolveAccount(
                    'revenue',
                    $projectArea,
                    $customer,
                    $item->revenue_type_id,
                    $revenueSegmentId
                ) ?? '41101010'; // Default placeholder

                $exportData[] = [
                    'doc_date' => $now->format('d.m.Y'),
                    'doc_type' => 'SA',
                    'company_code' => 'GDPS',
                    'posting_date' => $periodDate->endOfMonth()->format('d.m.Y'),
                    'period' => $periodDate->format('m'),
                    'currency' => 'IDR',
                    'reference' => $record->number,
                    'header_text' => 'Accrue '.($item->revenueType?->name ?? 'Revenue').' '.$periodDate->format('M Y'),
                    'accrued_account' => $accruedAccount,
                    'revenue_account' => $revenueAccount,
                    'amount' => $item->amount_estimated,
                    'assignment' => $record->project?->project_number ?? $record->number,
                    'text' => $record->project?->name ?? $record->number,
                    'business_area' => $projectArea?->code ?? '',
                    'cost_center' => $projectArea?->code ?? '',
                    'profit_center' => $record->project?->revenueSegment?->code ?? '',
                ];
            }
        }

        return view('finance::exports.sap-accrual-excel', [
            'data' => $exportData,
        ]);
    }

    public function title(): string
    {
        return 'SAP Accrual Export';
    }
}
