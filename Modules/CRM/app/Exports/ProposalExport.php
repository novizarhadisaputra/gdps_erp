<?php

namespace Modules\CRM\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\CRM\Models\Proposal;

class ProposalExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct(
        protected Proposal $record
    ) {}

    public function view(): View
    {
        return view('crm::exports.proposal-excel', [
            'record' => $this->record,
        ]);
    }

    public function title(): string
    {
        return 'Proposal - ' . $this->record->proposal_number;
    }
}
