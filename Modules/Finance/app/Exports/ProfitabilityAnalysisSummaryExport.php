<?php

namespace Modules\Finance\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Finance\Models\ProfitabilityAnalysis;

class ProfitabilityAnalysisSummaryExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct(
        protected ProfitabilityAnalysis $record
    ) {}

    public function view(): View
    {
        return view('finance::filament.clusters.finance.resources.profitability-analyses.pages.summary', [
            'record' => $this->record,
            'isExport' => true,
        ]);
    }

    public function title(): string
    {
        return 'Profitability Summary - '.($this->record->document_number ?? $this->record->id);
    }
}
