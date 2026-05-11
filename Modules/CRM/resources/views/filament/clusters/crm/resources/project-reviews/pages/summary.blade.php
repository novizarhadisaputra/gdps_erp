<x-filament-panels::page>
    <div class="space-y-8 pb-12">
        {{-- Header / Project Context --}}
        <div
            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-primary-50 dark:bg-primary-950/30 rounded-lg">
                    <x-heroicon-o-document-magnifying-glass class="w-8 h-8 text-primary-600" />
                </div>
                <div>
                    <h2 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight">Project Review
                        Summary</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Monitoring for: <span
                            class="text-gray-900 dark:text-gray-200 font-bold">{{ $record->lead?->customer?->name ?? 'N/A' }}</span>
                    </p>
                </div>
            </div>
            <div class="flex flex-col items-center md:items-end">
                <p class="text-[9px] text-gray-400 uppercase font-bold tracking-widest mt-2 whitespace-nowrap">Revision
                    No. {{ $record->revision_number ?? 0 }} | {{ $record->updated_at?->format('d M Y') ?? 'N/A' }}</p>
            </div>
        </div>

        {{-- 2-Column Modular Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- Card 1: General Information --}}
            <div class="flex flex-col h-full">
                <div
                    class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm overflow-hidden flex flex-col h-full border-t-4 border-t-blue-600 transition hover:shadow-md">
                    <div class="p-6 flex-1">
                        <div class="flex justify-between items-start mb-6">
                            <div class="p-2 bg-blue-50 dark:bg-blue-950/30 rounded-lg">
                                <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600" />
                            </div>
                            <span
                                class="text-[9px] font-black text-blue-600 uppercase tracking-widest bg-blue-50 dark:bg-blue-950/30 px-2 py-1 rounded">Module
                                GI</span>
                        </div>

                        <h3 class="text-base font-black text-gray-900 dark:text-white uppercase mb-4 leading-tight">
                            General Information</h3>

                        @if ($record->generalInformation)
                            <div class="space-y-4">
                                <div class="flex flex-col border-b border-gray-50 dark:border-gray-800 pb-2">
                                    <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Document
                                        No.</span>
                                    <span
                                        class="text-xs font-black text-gray-700 dark:text-gray-300 tabular-nums uppercase">{{ $record->generalInformation->number }}</span>
                                </div>
                                <div class="flex flex-col border-b border-gray-50 dark:border-gray-800 pb-2">
                                    <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Customer
                                        Area</span>
                                    <span
                                        class="text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">{{ $record->generalInformation->projectArea?->name ?: 'N/A' }}</span>
                                </div>
                                <div class="flex flex-col border-b border-gray-50 dark:border-gray-800 pb-2">
                                    <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Estimated
                                        Period</span>
                                    <span
                                        class="text-xs font-bold text-gray-700 dark:text-gray-300 tabular-nums uppercase">
                                        {{ $record->generalInformation->estimated_start_date?->format('d/m/Y') }} —
                                        {{ $record->generalInformation->estimated_end_date?->format('d/m/Y') }}
                                    </span>
                                </div>
                                <div class="flex flex-col border-b border-gray-50 dark:border-gray-800 pb-2">
                                    <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Work
                                        Scheme</span>
                                    <span
                                        class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">{{ $record->generalInformation->workScheme?->name ?: 'N/A' }}</span>
                                </div>

                                {{-- Relative Files --}}
                                @php
                                    $giMedia = [
                                        'TOR' => $record->generalInformation->getFirstMedia('tor'),
                                        'RFP' => $record->generalInformation->getFirstMedia('rfp'),
                                        'RFQ' => $record->generalInformation->getFirstMedia('rfq'),
                                    ];
                                    $hasGIMedia = collect($giMedia)->filter()->isNotEmpty();
                                @endphp
                                @if ($hasGIMedia)
                                    <div class="mt-4 pt-4 border-t border-gray-50 dark:border-gray-800">
                                        <span
                                            class="text-[8px] font-bold text-gray-400 uppercase tracking-widest mb-2 block">Related
                                            Files</span>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($giMedia as $label => $media)
                                                @if ($media)
                                                    <a href="{{ $media->getUrl() }}" target="_blank"
                                                        class="flex items-center gap-1 px-2 py-1 bg-gray-50 dark:bg-gray-800/40 rounded border border-gray-100 dark:border-gray-700 text-[9px] font-bold text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-800 transition">
                                                        <x-heroicon-m-paper-clip class="w-3 h-3 text-blue-500" />
                                                        {{ $label }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="flex flex-col mt-4 pt-2">
                                    <span
                                        class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Status</span>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span
                                            class="inline-block w-2.5 h-2.5 rounded-full bg-blue-600 shadow-[0_0_8px_rgba(37,99,235,0.4)]"></span>
                                        <span
                                            class="text-xs font-black text-blue-600 uppercase">{{ $record->generalInformation->status?->getLabel() ?: 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex-1 flex flex-col justify-center items-center py-12">
                                <x-heroicon-o-document-minus class="w-12 h-12 text-gray-200 dark:text-gray-800 mb-3" />
                                <p
                                    class="text-[10px] font-bold text-gray-300 dark:text-gray-700 uppercase tracking-[0.2em] italic">
                                    No GI Linked</p>
                            </div>
                        @endif
                    </div>

                    @if ($record->generalInformation)
                        <div
                            class="p-4 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-3">
                            <div class="flex flex-col gap-2 mb-2">
                                <button type="button" @click="$dispatch('open-modal', { id: 'preview-gi' })"
                                    class="w-full flex items-center justify-center gap-2 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black uppercase rounded-lg shadow-sm hover:shadow transition-all duration-300">
                                    <x-heroicon-m-eye class="w-4 h-4" />
                                    Preview Detail
                                </button>

                                <div class="grid grid-cols-2 gap-2">
                                    {{ $this->getAction('approveGI') }}
                                    {{ $this->getAction('rejectGI') }}
                                </div>
                            </div>
                            <a href="{{ route('filament.admin.crm.resources.leads.general-informations.view', ['lead' => $record->lead_id, 'record' => $record->general_information_id]) }}"
                                target="_blank"
                                class="flex items-center justify-center gap-1.5 text-[9px] font-bold uppercase text-gray-400 hover:text-blue-600 transition-colors">
                                View Full Page
                                <x-heroicon-m-arrow-top-right-on-square class="w-3 h-3" />
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card 2: Profitability Analysis --}}
            <div class="flex flex-col h-full">
                <div
                    class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm overflow-hidden flex flex-col h-full border-t-4 border-t-emerald-600 transition hover:shadow-md">
                    <div class="p-6 flex-1">
                        <div class="flex justify-between items-start mb-6">
                            <div class="p-2 bg-emerald-50 dark:bg-emerald-950/30 rounded-lg">
                                <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-600" />
                            </div>
                            <span
                                class="text-[9px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 dark:bg-emerald-950/30 px-2 py-1 rounded">Module
                                PA</span>
                        </div>

                        <h3 class="text-base font-black text-gray-900 dark:text-white uppercase mb-4 leading-tight">
                            Profitability Analysis</h3>

                        @if ($record->profitabilityAnalysis)
                            <div
                                class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-100 dark:border-gray-800 grid grid-cols-2 gap-4 mb-6">
                                <div>
                                    <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">EBITDA
                                    </p>
                                    <p class="text-xs font-black text-gray-900 dark:text-white tabular-nums">
                                        @money($record->profitabilityAnalysis->ebitda, 'IDR')</p>
                                </div>
                                <div>
                                    <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Net
                                        Margin</p>
                                    <p class="text-xs font-black text-emerald-600 tabular-nums">
                                        {{ number_format($record->profitabilityAnalysis->net_profit_margin, 2) }}%</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex flex-col border-b border-gray-50 dark:border-gray-800 pb-2">
                                    <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Document
                                        No.</span>
                                    <span
                                        class="text-xs font-black text-gray-700 dark:text-gray-300 tabular-nums uppercase">{{ $record->profitabilityAnalysis->number }}</span>
                                </div>
                                <div class="flex flex-col border-b border-gray-50 dark:border-gray-800 pb-2">
                                    <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Monthly
                                        Revenue</span>
                                    <span
                                        class="text-xs font-bold text-gray-700 dark:text-gray-300 tabular-nums uppercase">@money($record->profitabilityAnalysis->revenue_per_month, 'IDR')</span>
                                </div>
                                <div
                                    class="flex justify-between items-center py-2 px-3 bg-emerald-50/50 dark:bg-emerald-950/20 rounded-lg border border-emerald-100 dark:border-emerald-900/40">
                                    <span
                                        class="text-[8px] font-black text-emerald-600 uppercase tracking-widest">Margin
                                        Approved</span>
                                    <span
                                        class="text-[10px] font-black @if ($record->profitabilityAnalysis->is_margin_approved) text-emerald-600 @else text-amber-600 @endif uppercase">
                                        {{ $record->profitabilityAnalysis->is_margin_approved ? 'YES' : 'PENDING' }}
                                    </span>
                                </div>

                                {{-- Relative Files --}}
                                @php
                                    $paMedia = [
                                        'COGS' => $record->profitabilityAnalysis->getFirstMedia('cogs_source'),
                                        'TOR' => $record->profitabilityAnalysis->getFirstMedia('tor'),
                                        'RFP' => $record->profitabilityAnalysis->getFirstMedia('rfp'),
                                    ];
                                    $hasPAMedia = collect($paMedia)->filter()->isNotEmpty();
                                @endphp
                                @if ($hasPAMedia)
                                    <div class="mt-4 pt-4 border-t border-gray-50 dark:border-gray-800">
                                        <span
                                            class="text-[8px] font-bold text-gray-400 uppercase tracking-widest mb-2 block">Analysis
                                            Assets</span>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($paMedia as $label => $media)
                                                @if ($media)
                                                    <a href="{{ $media->getUrl() }}" target="_blank"
                                                        class="flex items-center gap-1 px-2 py-1 bg-gray-50 dark:bg-gray-800/40 rounded border border-gray-100 dark:border-gray-700 text-[9px] font-bold text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-800 transition">
                                                        <x-heroicon-m-paper-clip class="w-3 h-3 text-emerald-500" />
                                                        {{ $label }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="flex flex-col mt-4 pt-2">
                                    <span
                                        class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Status</span>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span
                                            class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-600 shadow-[0_0_8px_rgba(5,150,105,0.4)]"></span>
                                        <span
                                            class="text-xs font-black text-emerald-600 uppercase">{{ $record->profitabilityAnalysis->status?->getLabel() ?: 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex-1 flex flex-col justify-center items-center py-12">
                                <x-heroicon-o-document-minus class="w-12 h-12 text-gray-200 dark:text-gray-800 mb-3" />
                                <p
                                    class="text-[10px] font-bold text-gray-300 dark:text-gray-700 uppercase tracking-[0.2em] italic">
                                    No PA Linked</p>
                            </div>
                        @endif
                    </div>

                    @if ($record->profitabilityAnalysis)
                        <div
                            class="p-4 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-3">
                            <div class="flex flex-col gap-2 mb-2">
                                <button type="button" @click="$dispatch('open-modal', { id: 'preview-pa' })"
                                    class="w-full flex items-center justify-center gap-2 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-black uppercase rounded-lg shadow-sm hover:shadow transition-all duration-300">
                                    <x-heroicon-m-eye class="w-4 h-4" />
                                    Preview Detail
                                </button>

                                <div class="flex flex-col gap-2">
                                    <div class="grid grid-cols-2 gap-2">
                                        {{ $this->getAction('approveProject') }}
                                        {{ $this->getAction('rejectProject') }}
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        {{ $this->getAction('approvePA') }}
                                        {{ $this->getAction('rejectPA') }}
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('filament.admin.crm.resources.leads.profitability-analyses.view', ['lead' => $record->lead_id, 'record' => $record->profitability_analysis_id]) }}"
                                target="_blank"
                                class="flex items-center justify-center gap-1.5 text-[9px] font-bold uppercase text-gray-400 hover:text-emerald-600 transition-colors">
                                View Full Analysis
                                <x-heroicon-m-arrow-top-right-on-square class="w-3 h-3" />
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card 3: Proposal --}}
            <div class="flex flex-col h-full">
                <div
                    class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm overflow-hidden flex flex-col h-full border-t-4 border-t-amber-500 transition hover:shadow-md">
                    <div class="p-6 flex-1">
                        <div class="flex justify-between items-start mb-6">
                            <div class="p-2 bg-amber-50 dark:bg-amber-950/30 rounded-lg">
                                <x-heroicon-o-presentation-chart-line class="w-5 h-5 text-amber-600" />
                            </div>
                            <span
                                class="text-[9px] font-black text-amber-600 uppercase tracking-widest bg-amber-50 dark:bg-amber-950/30 px-2 py-1 rounded">Module
                                Proposal</span>
                        </div>

                        <h3 class="text-base font-black text-gray-900 dark:text-white uppercase mb-4 leading-tight">
                            Sales Proposal</h3>

                        @if ($record->proposal)
                            <div class="space-y-4">
                                <div class="flex flex-col border-b border-gray-50 dark:border-gray-800 pb-2">
                                    <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Proposal
                                        ID</span>
                                    <span
                                        class="text-xs font-black text-gray-700 dark:text-gray-300 tabular-nums uppercase">{{ $record->proposal->number }}</span>
                                </div>
                                <div class="flex flex-col border-b border-gray-50 dark:border-gray-800 pb-2">
                                    <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Project
                                        Title</span>
                                    <span
                                        class="text-xs font-bold text-gray-700 dark:text-gray-300 line-clamp-2 uppercase min-h-[2.5rem]">{{ $record->proposal->title }}</span>
                                </div>
                                <div class="flex flex-col border-b border-gray-50 dark:border-gray-800 pb-2">
                                    <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Total
                                        Value</span>
                                    <span
                                        class="text-base font-black text-gray-900 dark:text-white tabular-nums">@money($record->proposal->amount, 'IDR')</span>
                                </div>

                                {{-- Relative Files --}}
                                @php
                                    $proposalMedia = [
                                        'Final' => $record->proposal->getFirstMedia('final_proposal'),
                                        'Signed' => $record->proposal->getFirstMedia('signed_proposal'),
                                    ];
                                    $hasProposalMedia = collect($proposalMedia)->filter()->isNotEmpty();
                                @endphp
                                @if ($hasProposalMedia)
                                    <div class="mt-4 pt-4 border-t border-gray-50 dark:border-gray-800">
                                        <span
                                            class="text-[8px] font-bold text-gray-400 uppercase tracking-widest mb-2 block">Proposal
                                            Documents</span>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($proposalMedia as $label => $media)
                                                @if ($media)
                                                    <a href="{{ $media->getUrl() }}" target="_blank"
                                                        class="flex items-center gap-1 px-2 py-1 bg-gray-50 dark:bg-gray-800/40 rounded border border-gray-100 dark:border-gray-700 text-[9px] font-bold text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-800 transition">
                                                        <x-heroicon-m-paper-clip class="w-3 h-3 text-amber-500" />
                                                        {{ $label }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="flex flex-col mt-4 pt-2">
                                    <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Proposal
                                        Status</span>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span
                                            class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.4)]"></span>
                                        <span
                                            class="text-xs font-black text-amber-600 uppercase">{{ $record->proposal->status?->getLabel() ?: 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex-1 flex flex-col justify-center items-center py-12">
                                <x-heroicon-o-document-minus class="w-12 h-12 text-gray-200 dark:text-gray-800 mb-3" />
                                <p
                                    class="text-[10px] font-bold text-gray-300 dark:text-gray-700 uppercase tracking-[0.2em] italic">
                                    No Proposal Linked</p>
                            </div>
                        @endif
                    </div>

                    @if ($record->proposal)
                        <div
                            class="p-4 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-3">
                            <div class="flex flex-col gap-2 mb-2">
                                <button type="button" @click="$dispatch('open-modal', { id: 'preview-proposal' })"
                                    class="w-full flex items-center justify-center gap-2 py-2 bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-black uppercase rounded-lg shadow-sm hover:shadow transition-all duration-300">
                                    <x-heroicon-m-eye class="w-4 h-4" />
                                    Preview Detail
                                </button>

                                <div class="grid grid-cols-2 gap-2">
                                    {{ $this->getAction('approveProposal') }}
                                    {{ $this->getAction('rejectProposal') }}
                                </div>
                            </div>
                            <a href="{{ route('filament.admin.crm.resources.leads.proposals.view', ['lead' => $record->lead_id, 'record' => $record->proposal_id]) }}"
                                target="_blank"
                                class="flex items-center justify-center gap-1.5 text-[9px] font-bold uppercase text-gray-400 hover:text-amber-600 transition-colors">
                                View Full Record
                                <x-heroicon-m-arrow-top-right-on-square class="w-3 h-3" />
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card 4: Risk Register --}}
            <div class="flex flex-col h-full">
                <div
                    class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm overflow-hidden flex flex-col h-full border-t-4 border-t-rose-600 transition hover:shadow-md">
                    <div class="p-6 flex-1">
                        <div class="flex justify-between items-start mb-6">
                            <div class="p-2 bg-rose-50 dark:bg-rose-950/30 rounded-lg">
                                <x-heroicon-o-shield-exclamation class="w-5 h-5 text-rose-600" />
                            </div>
                            <span
                                class="text-[9px] font-black text-rose-600 uppercase tracking-widest bg-rose-50 dark:bg-rose-950/30 px-2 py-1 rounded">Module
                                RR</span>
                        </div>

                        <h3 class="text-base font-black text-gray-900 dark:text-white uppercase mb-4 leading-tight">
                            Risk Register</h3>

                        @if ($record->generalInformation)
                            <div class="space-y-4">
                                <div class="flex flex-col border-b border-gray-50 dark:border-gray-800 pb-2">
                                    <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Document
                                        No.</span>
                                    <span
                                        class="text-xs font-black text-gray-700 dark:text-gray-300 tabular-nums uppercase">{{ $record->generalInformation->rr_document_number ?: 'NOT SUBMITTED' }}</span>
                                </div>
                                <div class="flex flex-col border-b border-gray-50 dark:border-gray-800 pb-2">
                                    <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Submission
                                        ID</span>
                                    <span
                                        class="text-xs font-medium text-gray-700 dark:text-gray-300 tabular-nums uppercase">{{ $record->generalInformation->rr_submission_id ?: 'N/A' }}</span>
                                </div>

                                <div class="flex flex-col mt-4 pt-2">
                                    <span
                                        class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Status</span>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span
                                            class="inline-block w-2.5 h-2.5 rounded-full @if ($record->generalInformation->rr_status === 'approved') bg-emerald-600 shadow-[0_0_8px_rgba(5,150,105,0.4)] @else bg-rose-600 shadow-[0_0_8px_rgba(225,29,72,0.4)] @endif"></span>
                                        <span
                                            class="text-xs font-black @if ($record->generalInformation->rr_status === 'approved') text-emerald-600 @else text-rose-600 @endif uppercase">{{ $record->generalInformation->rr_status ?: 'DRAFT' }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex-1 flex flex-col justify-center items-center py-12">
                                <x-heroicon-o-document-minus class="w-12 h-12 text-gray-200 dark:text-gray-800 mb-3" />
                                <p
                                    class="text-[10px] font-bold text-gray-300 dark:text-gray-700 uppercase tracking-[0.2em] italic">
                                    No GI Linked</p>
                            </div>
                        @endif
                    </div>

                    @if ($record->generalInformation)
                        <div
                            class="p-4 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-3">
                            <div class="flex flex-col gap-2 mb-2">
                                @if($record->generalInformation->rr_document_path)
                                <a href="{{ $record->generalInformation->rr_document_path }}" target="_blank"
                                    class="w-full flex items-center justify-center gap-2 py-2 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-black uppercase rounded-lg shadow-sm hover:shadow transition-all duration-300">
                                    <x-heroicon-m-arrow-top-right-on-square class="w-4 h-4" />
                                    View Full Document
                                </a>
                                @endif

                                <a href="{{ route('filament.admin.crm.resources.leads.general-informations.view', ['lead' => $record->lead_id, 'record' => $record->general_information_id]) }}"
                                    class="w-full flex items-center justify-center gap-2 py-2 border border-rose-200 dark:border-rose-900/50 text-rose-600 dark:text-rose-400 text-[10px] font-black uppercase rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all duration-300">
                                    <x-heroicon-m-cog-6-tooth class="w-4 h-4" />
                                    Manage RR in GI
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Consolidated Signatures Area --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-8 shadow-sm">
            <div class="flex items-center gap-2 mb-10 border-b border-gray-100 dark:border-gray-800 pb-4">
                <div class="w-1.5 h-6 bg-primary-600 rounded-full"></div>
                <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Approval
                    Authorization Summary</h3>
            </div>

            @php
                $signatureService = app(\Modules\MasterData\Services\SignatureService::class);
                $allSignatures = collect();
                if ($record->generalInformation) {
                    $allSignatures = $allSignatures->merge(
                        $record->generalInformation->signatures->map(fn($s) => ['type' => 'GI', 'sig' => $s]),
                    );
                }
                if ($record->profitabilityAnalysis) {
                    $allSignatures = $allSignatures->merge(
                        $record->profitabilityAnalysis->signatures->map(fn($s) => ['type' => 'PA', 'sig' => $s]),
                    );
                }
                if ($record->proposal) {
                    $allSignatures = $allSignatures->merge(
                        $record->proposal->signatures->map(fn($s) => ['type' => 'Proposal', 'sig' => $s]),
                    );
                }

                $uniqueSignatures = $allSignatures->groupBy(
                    fn($item) => $item['sig']->user_id . '_' . ($item['sig']->signature_type instanceof \BackedEnum ? $item['sig']->signature_type->value : $item['sig']->signature_type),
                );
            @endphp

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-10">
                @forelse($uniqueSignatures as $group)
                    @php
                        $first = $group->first();
                        $sig = $first['sig'];
                    @endphp
                    <div class="flex flex-col items-center">
                        <p
                            class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-4 h-6 text-center leading-tight">
                            {{ $sig->signature_type instanceof \BackedEnum ? $sig->signature_type->getLabel() : ($sig->signature_type ?: 'Manual Approval') }}
                        </p>

                        @php
                            $qrUrl = $signatureService->createSignatureData(
                                $sig->user,
                                $sig->signable,
                                $sig->signature_type instanceof \BackedEnum ? $sig->signature_type->value : ($sig->signature_type ?: 'approved'),
                            );
                            $qrCode = $signatureService->generateQRCode($qrUrl);
                        @endphp
                        <div
                            class="mb-4 p-2 bg-gray-50/50 dark:bg-gray-800/30 rounded-xl border border-gray-100 dark:border-gray-800 group hover:border-primary-200 dark:hover:border-primary-900 transition-colors">
                            <img src="{{ $qrCode }}"
                                class="w-16 h-16 opacity-90 mix-blend-multiply dark:invert group-hover:opacity-100 transition-opacity"
                                alt="Signature QR">
                        </div>
                        <div class="w-full text-center">
                            <p
                                class="text-[10px] font-black text-gray-900 dark:text-white uppercase leading-tight mb-1">
                                {{ $sig->user->name }}
                            </p>
                            <p
                                class="text-[7px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-tighter italic">
                                {{ $sig->role }}
                            </p>
                            <div class="flex flex-wrap justify-center gap-1 mt-3">
                                @foreach ($group->pluck('type')->unique() as $docType)
                                    <span
                                        class="text-[6px] font-black uppercase px-2 py-0.5 rounded-full {{ match ($docType) {
                                            'GI' => 'bg-blue-50 text-blue-600 border border-blue-100',
                                            'PA' => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
                                            'Proposal' => 'bg-amber-50 text-amber-600 border border-amber-100',
                                            default => 'bg-gray-50',
                                        } }}">
                                        {{ $docType }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div
                        class="col-span-full py-16 text-center border-2 border-dashed border-gray-100 dark:border-gray-900 rounded-2xl">
                        <div
                            class="w-16 h-16 bg-gray-50 dark:bg-gray-800/50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <x-heroicon-o-shield-check class="w-8 h-8 text-gray-200 dark:text-gray-700" />
                        </div>
                        <p class="text-[11px] font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em]">
                            Pending Authorization</p>
                        <p class="text-[9px] text-gray-300 dark:text-gray-700 mt-2 font-medium italic">No verified
                            signatures recorded for this document review chain.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;700;800&display=swap');

        .tabular-nums {
            font-family: 'JetBrains Mono', monospace !important;
            font-variant-numeric: tabular-nums;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
    {{-- Custom Modals for Previews --}}
    <x-filament::modal id="preview-gi" width="7xl">
        <x-slot name="heading">
            General Information Detail
        </x-slot>
        <div class="py-4">
            {{ $this->giSchema }}
        </div>
    </x-filament::modal>

    <x-filament::modal id="preview-pa" width="7xl">
        <x-slot name="heading">
            Profitability Analysis Detail
        </x-slot>
        <div class="py-4">
            {{ $this->paSchema }}
        </div>
    </x-filament::modal>

    <x-filament::modal id="preview-proposal" width="7xl">
        <x-slot name="heading">
            Sales Proposal Detail
        </x-slot>
        <div class="py-4">
            {{ $this->proposalSchema }}
        </div>
    </x-filament::modal>
</x-filament-panels::page>
